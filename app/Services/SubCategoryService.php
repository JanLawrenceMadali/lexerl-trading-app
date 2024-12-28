<?php

namespace App\Services;

use App\Models\Product;
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

            Product::create([
                'subcategory_id' => $subcategory->id,
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

            Product::where('subcategory_id', $subcategory->id)->update([
                'subcategory_id' => $subcategory->id,
                'category_id' => $data['category_id'],
            ]);

            return $subcategory;
        });
    }

    public function deleteSubCategory(Subcategory $subcategory)
    {
        return DB::transaction(function () use ($subcategory) {
            $subcategory->delete();
            Product::where('subcategory_id', $subcategory->id)->delete();

            return true;
        });
    }
}
