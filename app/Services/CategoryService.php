<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    public function createCategory($data)
    {
        return DB::transaction(function () use ($data) {
            $category = Category::create(['name' => $data['name']]);

            return $category;
        });
    }

    public function updateCategory(Category $category, array $data)
    {
        return DB::transaction(function () use ($category, $data) {
            $category->update(['name' => $data['name']]);

            return $category;
        });
    }

    public function deleteCategory(Category $category)
    {
        return DB::transaction(function () use ($category) {
            $category->delete();

            return true;
        });
    }
}
