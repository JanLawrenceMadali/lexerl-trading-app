<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Services\ActivityLoggerService;
use App\Services\CategoryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    protected $categoryService;
    protected $activityLog;

    public function __construct(
        CategoryService $categoryService,
        ActivityLoggerService $activityLoggerService
    ) {
        $this->categoryService = $categoryService;
        $this->activityLog = $activityLoggerService;
    }

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
            $category = $this->categoryService->createCategory($validated);

            $this->activityLog->logCategoryAction(
                $category,
                ActivityLog::ACTION_CREATED,
                ['new' => $category->toArray()]
            );

            return redirect()->back()->with('success', 'Category created successfully');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to create category');
        }
    }

    public function update(CategoryRequest $categoryRequest, Category $category)
    {
        $validated = $categoryRequest->validated();

        try {
            $oldData = $category->toArray();

            $this->categoryService->updateCategory($category, $validated);

            $this->activityLog->logCategoryAction(
                $category,
                ActivityLog::ACTION_UPDATED,
                ['old' => $oldData, 'new' => $category->toArray()]
            );

            return redirect()->back()->with('success', 'Category updated successfully');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to create category');
        }
    }

    public function destroy(Category $category)
    {
        try {
            $this->categoryService->deleteCategory($category);

            $this->activityLog->logCategoryAction(
                $category,
                ActivityLog::ACTION_DELETED,
                ['old' => $category->toArray()]
            );

            return redirect()->back()->with('success', 'Category deleted successfully');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to create category');
        }
    }
}
