<?php

namespace App\Repositories;

use App\Services\JsonDatabase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;

abstract class BaseRepository
{
    protected JsonDatabase $jsonDb;

    abstract protected function model(): string;
    abstract protected function tableName(): string;

    public function __construct(JsonDatabase $jsonDb)
    {
        $this->jsonDb = $jsonDb;
    }

    protected function makeModel(array $attributes): Model
    {
        $class = $this->model();
        $model = new $class();

        foreach ($attributes as $key => $value) {
            $model->setAttribute($key, $value);
        }

        if (isset($attributes['id'])) {
            $model->id = (int)$attributes['id'];
        }

        $model->exists = true;
        $this->loadRelations($model, $attributes);

        return $model;
    }

    protected function loadRelations(Model $model, array $attributes): void
    {
        // Hydrate category if category_id exists
        if (isset($attributes['category_id'])) {
            $catRecord = $this->jsonDb->find('categories', (int)$attributes['category_id']);
            if ($catRecord) {
                $catModel = new \App\Models\Category($catRecord);
                $catModel->id = $catRecord['id'];
                $model->setRelation('category', $catModel);
            }
        }

        // Hydrate user if user_id exists
        if (isset($attributes['user_id'])) {
            $userRecord = $this->jsonDb->find('users', (int)$attributes['user_id']);
            if ($userRecord) {
                unset($userRecord['password']);
                $userModel = new \App\Models\User($userRecord);
                $userModel->id = $userRecord['id'];
                $model->setRelation('user', $userModel);
            }
        }

        // Hydrate lostItem if lost_item_id exists
        if (isset($attributes['lost_item_id'])) {
            $lostRecord = $this->jsonDb->find('lost_items', (int)$attributes['lost_item_id']);
            if ($lostRecord) {
                $lostModel = new \App\Models\LostItem($lostRecord);
                $lostModel->id = $lostRecord['id'];
                if (isset($lostRecord['category_id'])) {
                    $catRecord = $this->jsonDb->find('categories', (int)$lostRecord['category_id']);
                    if ($catRecord) {
                        $catModel = new \App\Models\Category($catRecord);
                        $catModel->id = $catRecord['id'];
                        $lostModel->setRelation('category', $catModel);
                    }
                }
                $model->setRelation('lostItem', $lostModel);
            }
        }

        // Hydrate foundItem if found_item_id exists
        if (isset($attributes['found_item_id'])) {
            $foundRecord = $this->jsonDb->find('found_items', (int)$attributes['found_item_id']);
            if ($foundRecord) {
                $foundModel = new \App\Models\FoundItem($foundRecord);
                $foundModel->id = $foundRecord['id'];
                if (isset($foundRecord['category_id'])) {
                    $catRecord = $this->jsonDb->find('categories', (int)$foundRecord['category_id']);
                    if ($catRecord) {
                        $catModel = new \App\Models\Category($catRecord);
                        $catModel->id = $catRecord['id'];
                        $foundModel->setRelation('category', $catModel);
                    }
                }
                $model->setRelation('foundItem', $foundModel);
            }
        }
    }

    public function all(array $columns = ['*']): Collection
    {
        $records = $this->jsonDb->all($this->tableName());
        $models = array_map(fn($r) => $this->makeModel($r), $records);
        return new Collection($models);
    }

    public function find(int $id): ?Model
    {
        $record = $this->jsonDb->find($this->tableName(), $id);
        return $record ? $this->makeModel($record) : null;
    }

    public function create(array $data): Model
    {
        foreach ($data as $k => $v) {
            if ($v instanceof \Illuminate\Http\UploadedFile) {
                unset($data[$k]);
            }
        }

        $record = $this->jsonDb->insert($this->tableName(), $data);
        return $this->makeModel($record);
    }

    public function update(Model $model, array $data): Model
    {
        $record = $this->jsonDb->update($this->tableName(), $model->id, $data);
        return $this->makeModel($record ?: array_merge($model->toArray(), $data));
    }

    public function delete(Model $model): bool
    {
        return $this->jsonDb->delete($this->tableName(), $model->id);
    }

    public function deleteById(int $id): bool
    {
        return $this->jsonDb->delete($this->tableName(), $id);
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        $records = array_reverse($this->jsonDb->all($this->tableName()));
        $models = array_map(fn($r) => $this->makeModel($r), $records);

        return $this->paginateArray($models, $perPage);
    }

    protected function paginateArray(array $items, int $perPage = 15): LengthAwarePaginator
    {
        $page = Paginator::resolveCurrentPage() ?: 1;
        $total = count($items);
        $results = array_slice($items, ($page - 1) * $perPage, $perPage);

        return new Paginator($results, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
        ]);
    }
}
