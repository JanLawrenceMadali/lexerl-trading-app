<?php

namespace App\Services;

use App\Models\Subcategory;
use Illuminate\Support\Facades\DB;

class SubCategoryService
{
    public function createSubCategory($data)
    {
        return DB::transaction(function () use ($data) {

            $subcategory = Subcategory::create([
                'name' => $data['name'],
                'category_id' => $data['category_id'],
            ]);

            return $subcategory;
        });
    }

    public function updateSubCategory(Subcategory $subcategory, array $data)
    {
        return DB::transaction(function () use ($subcategory, $data) {

            $subcategory->update([
                'name' => $data['name'],
                'category_id' => $data['category_id'],
            ]);

            return $subcategory;
        });
    }

    public function deleteSubCategory(Subcategory $subcategory)
    {
        return DB::transaction(function () use ($subcategory) {
            $subcategory->delete();

            return true;
        });
    }
}
