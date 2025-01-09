<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\ActivityLog;
use App\Http\Requests\SubcategoryRequest;
use App\Services\ActivityLoggerService;
use App\Services\SubCategoryService;
use Illuminate\Support\Facades\Auth;

class SubCategoryController extends Controller
{
    protected $subcategoryService;
    protected $activityLog;
    private $actor;

    public function __construct(
        SubCategoryService $subCategoryService,
        ActivityLoggerService $activityLoggerService
    ) {
        $this->subcategoryService = $subCategoryService;
        $this->activityLog = $activityLoggerService;
        $this->actor = Auth::user()->username;
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
                ActivityLog::ACTION_CREATED,
                "{$this->actor} created a new sub category: {$subcategory->name}",
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
                ActivityLog::ACTION_UPDATED,
                "{$this->actor} updated a sub category: {$subcategory->name}",
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
                ActivityLog::ACTION_DELETED,
                "{$this->actor} deleted a sub category: {$subcategory->name}",
                ['old' => $subcategory->toArray()]
            );

            return redirect()->back()->with('success', 'Sub Category deleted successfully');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to create sub category');
        }
    }
}
