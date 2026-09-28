<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

abstract class ItemRecordRepository extends BaseRepository
{
    abstract protected function itemType(): string;

    protected function rows(): Builder
    {
        return DB::table('item_records')->where('type', $this->itemType());
    }

    protected function records(): array
    {
        return $this->rows()->orderBy('item_id')->get(['item_id', 'attributes'])->map(function ($row) {
            $data = json_decode($row->attributes, true, 512, JSON_THROW_ON_ERROR);
            $data['id'] = (int) $row->item_id;

            return $data;
        })->all();
    }

    public function all(array $columns = ['*']): Collection
    {
        return new Collection(array_map(fn ($row) => $this->makeModel($row), $this->records()));
    }

    public function find(int $id): ?Model
    {
        $row = $this->rows()->where('item_id', $id)->first(['attributes']);
        if (! $row) {
            return null;
        }

        $data = json_decode($row->attributes, true, 512, JSON_THROW_ON_ERROR);
        $data['id'] = $id;

        return $this->makeModel($data);
    }

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            DB::table('item_sequences')->where('type', $this->itemType())->increment('last_id');
            $id = (int) DB::table('item_sequences')->where('type', $this->itemType())->value('last_id');
            $data['id'] = $id;
            $data['created_at'] ??= now()->toDateTimeString();
            $data['updated_at'] ??= now()->toDateTimeString();
            $this->insertRecord($data);

            return $this->makeModel($data);
        });
    }

    public function import(array $data): void
    {
        DB::transaction(function () use ($data) {
            $this->insertRecord($data);
            DB::table('item_sequences')->where('type', $this->itemType())
                ->where('last_id', '<', $data['id'])
                ->update(['last_id' => $data['id']]);
        });
    }

    private function insertRecord(array $data): void
    {
        $image = $data['image_data'] ?? null;
        $mime = $data['image_mime'] ?? null;
        unset($data['image_data'], $data['image_mime']);

        DB::table('item_records')->insert([
            'type' => $this->itemType(),
            'item_id' => $data['id'],
            'attributes' => json_encode($data, JSON_THROW_ON_ERROR),
            'image_data' => $image,
            'image_mime' => $mime,
        ]);
    }

    public function update(Model $model, array $data): Model
    {
        $row = $this->rows()->where('item_id', $model->id)->first(['attributes']);
        if (! $row) {
            throw new \RuntimeException('Item record not found.');
        }

        $attributes = json_decode($row->attributes, true, 512, JSON_THROW_ON_ERROR);
        $image = $data['image_data'] ?? null;
        $mime = $data['image_mime'] ?? null;
        unset($data['image_data'], $data['image_mime']);
        $attributes = array_merge($attributes, $data, ['updated_at' => now()->toDateTimeString()]);
        $changes = ['attributes' => json_encode($attributes, JSON_THROW_ON_ERROR)];
        if ($image !== null) {
            $changes['image_data'] = $image;
            $changes['image_mime'] = $mime;
        }

        $this->rows()->where('item_id', $model->id)->update($changes);

        return $this->makeModel($attributes);
    }

    public function delete(Model $model): bool
    {
        return $this->deleteById($model->id);
    }

    public function deleteById(int $id): bool
    {
        return $this->rows()->where('item_id', $id)->delete() > 0;
    }

    public function paginate(int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->paginateArray(array_reverse($this->all()->all()), $perPage);
    }

    public function image(int $id): ?object
    {
        return $this->rows()->where('item_id', $id)->first(['image_data', 'image_mime']);
    }
}
