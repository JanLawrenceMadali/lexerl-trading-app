<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\ActivityLog;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::latest()->get();
        return inertia('Settings/Category/Index', [
            'categories' => $categories,
        ]);
    }

    public function store(CategoryRequest $categoryRequest)
    {
        $validated = $categoryRequest->validated();

        try {
            DB::transaction(function () use ($validated) {
                Category::create($validated);

                $this->logs('Category Created');
            });
            return redirect()->route('categories')->with('success', 'Category created successfully');
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function update(CategoryRequest $categoryRequest, Category $category)
    {
        $validated = $categoryRequest->validated();

        try {
            DB::transaction(function () use ($validated, $category) {
                $category->update($validated);

                $this->logs('Category Updated');
            });
            return redirect()->route('categories')->with('success', 'Category updated successfully');
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function destroy(Category $category)
    {
        try {
            DB::transaction(function () use ($category) {
                $category->delete();

                $this->logs('Category Deleted');
            });
            return redirect()->route('categories')->with('success', 'Category deleted successfully');
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
