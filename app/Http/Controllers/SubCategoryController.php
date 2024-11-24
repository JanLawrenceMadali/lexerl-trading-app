<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\SubcategoryRequest;
use Illuminate\Support\Facades\Auth;

class SubCategoryController extends Controller
{
    public function index()
    {
        $categories = Category::latest()->get();
        $subcategories = Subcategory::latest()->get();
        return inertia('Settings/Subcategory/Index', [
            'subcategories' => $subcategories,
            'categories' => $categories,
        ]);
    }

    public function store(SubcategoryRequest $subcategoryRequest)
    {
        $validated = $subcategoryRequest->validated();

        try {
            DB::transaction(function () use ($validated) {
                Subcategory::create($validated);

                $this->logs('Sub Category Created');
            });
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function logs(string $action)
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $action . ' by ' . Auth::user()->username,
        ]);
    }
}
