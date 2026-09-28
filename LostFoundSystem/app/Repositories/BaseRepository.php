<?php

namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;

/**
 * BaseRepository — thin Eloquent-backed CRUD layer.
 *
 * Every concrete repository reads and writes through its Eloquent model,
 * so all data (users, categories, claims, matches, notifications, audit
 * logs, etc.) is persisted in the configured relational database
 * (MySQL/SQLite/etc. per DB_CONNECTION) rather than flat JSON files.
 */
abstract class BaseRepository
{
    abstract protected function model(): string;

    /**
     * The underlying database table, derived from the Eloquent model
     * unless a subclass overrides it.
     */
    protected function tableName(): string
    {
        return $this->newModel()->getTable();
    }

    protected function newModel(): Model
    {
        $class = $this->model();

        return new $class();
    }

    protected function query(): Builder
    {
        return $this->newModel()->newQuery();
    }

    /**
     * Build a detached model instance from a raw attribute array.
     *
     * Used by repositories such as ItemRecordRepository that hydrate models
     * from a JSON-blob table (item_records) instead of native Eloquent
     * columns, so the usual query builder can't be used to load them.
     */
    protected function makeModel(array $attributes): Model
    {
        $class = $this->model();
        $model = new $class();

        foreach ($attributes as $key => $value) {
            $model->setAttribute($key, $value);
        }

        if (isset($attributes['id'])) {
            $model->id = (int) $attributes['id'];
        }

        $model->exists = true;
        $this->loadRelations($model, $attributes);

        return $model;
    }

    /**
     * Bridge relations that Eloquent cannot resolve on its own because lost
     * and found item data lives in the `item_records` JSON-blob table
     * rather than the (unused) `lost_items` / `found_items` columns.
     *
     * category()/user() relations resolve normally through Eloquent since
     * categories and users are stored in real, populated database tables.
     */
    protected function loadRelations(Model $model, array $attributes): void
    {
        if (isset($attributes['lost_item_id'])) {
            $lostModel = app(LostItemRepository::class)->find((int) $attributes['lost_item_id']);
            if ($lostModel) {
                $model->setRelation('lostItem', $lostModel);
            }
        }

        if (isset($attributes['found_item_id'])) {
            $foundModel = app(FoundItemRepository::class)->find((int) $attributes['found_item_id']);
            if ($foundModel) {
                $model->setRelation('foundItem', $foundModel);
            }
        }
    }

    public function all(array $columns = ['*']): Collection
    {
        return $this->query()->get($columns)->map(fn (Model $model) => $this->hydrateRelations($model));
    }

    public function find(int $id): ?Model
    {
        $model = $this->query()->find($id);

        return $model ? $this->hydrateRelations($model) : null;
    }

    /**
     * Bridge found_item_id/lost_item_id relations for models fetched
     * through normal Eloquent queries (e.g. Claim, ItemMatch), since their
     * native belongsTo relations point at the always-empty found_items/
     * lost_items tables.
     */
    protected function hydrateRelations(Model $model): Model
    {
        $this->loadRelations($model, $model->getAttributes());

        return $model;
    }

    public function create(array $data): Model
    {
        foreach ($data as $key => $value) {
            if ($value instanceof UploadedFile) {
                unset($data[$key]);
            }
        }

        return $this->hydrateRelations($this->model()::create($data));
    }

    public function update(Model $model, array $data): Model
    {
        $model->fill($data);
        $model->save();

        return $model;
    }

    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }

    public function deleteById(int $id): bool
    {
        $model = $this->find($id);

        return $model ? $this->delete($model) : false;
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        $paginator = $this->query()->latest('id')->paginate($perPage);
        $paginator->getCollection()->transform(fn (Model $model) => $this->hydrateRelations($model));

        return $paginator;
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
