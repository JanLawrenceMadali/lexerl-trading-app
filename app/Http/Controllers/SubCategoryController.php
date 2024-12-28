<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\ActivityLog;
use App\Http\Requests\SubcategoryRequest;
use App\Services\ActivityLoggerService;
use App\Services\SubCategoryService;

class SubCategoryController extends Controller
{
    protected $subcategoryService;
    protected $activityLog;

    public function __construct(
        SubCategoryService $subCategoryService,
        ActivityLoggerService $activityLoggerService
    ) {
        $this->subcategoryService = $subCategoryService;
        $this->activityLog = $activityLoggerService;
    }
    public function index()
    {
        $categories = Category::latest()->get();
        $subcategories = Subcategory::with('categories')->latest()->get();
        return inertia('Settings/Subcategory/Index', [
            'subcategories' => $subcategories,
            'categories' => $categories,
        ]);
    }

    public function store(SubcategoryRequest $subcategoryRequest)
    {
        $validated = $subcategoryRequest->validated();

        try {
            $subcategory = $this->subcategoryService->createSubCategory($validated);

            $this->activityLog->logSubCategoryAction(
                $subcategory,
                ActivityLog::ACTION_CREATED,
                ['new' => $subcategory->toArray()]
            );

            return redirect()->back()->with('success', 'Sub Category created successfully');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to create sub category');
        }
    }

    public function update(SubcategoryRequest $subcategoryRequest, Subcategory $subcategory)
    {
        $validated = $subcategoryRequest->validated();

        try {
            $oldData = $subcategory->toArray();

            $this->subcategoryService->updateSubCategory($subcategory, $validated);

            $this->activityLog->logSubCategoryAction(
                $subcategory,
                ActivityLog::ACTION_UPDATED,
                ['old' => $oldData, 'new' => $subcategory->toArray()]
            );

            return redirect()->back()->with('success', 'Sub Category updated successfully');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to create sub category');
        }
    }

    public function destroy(Subcategory $subcategory)
    {
        try {
            $this->subcategoryService->deleteSubCategory($subcategory);

            $this->activityLog->logSubCategoryAction(
                $subcategory,
                ActivityLog::ACTION_DELETED,
                ['old' => $subcategory->toArray()]
            );

            return redirect()->back()->with('success', 'Sub Category deleted successfully');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to create sub category');
        }
    }
}
