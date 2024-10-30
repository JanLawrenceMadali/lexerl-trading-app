<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubcategoryRequest;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return inertia('Settings/Subcategory/Index', [
            'categories' => $categories
        ]);
    }

    public function store(SubcategoryRequest $subcategoryRequest)
    {
        $validated = $subcategoryRequest->validated();

        Subcategory::create($validated);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Created a sub category',
            'description' => 'A sub category was created by ' . auth()->user()->username,
        ]);
    }
}
