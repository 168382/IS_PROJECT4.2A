<?php

namespace App\Repositories;

use App\Models\Category;

class CategoryRepository extends BaseRepository
{
    protected function model(): string
    {
        return Category::class;
    }

    protected function tableName(): string
    {
        return 'categories';
    }

    public function allOrdered()
    {
        $categories = $this->all();
        return $categories->sortBy('name');
    }
}
