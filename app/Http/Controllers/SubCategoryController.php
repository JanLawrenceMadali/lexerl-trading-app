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
            DB::transaction(function () use ($validated) {
                Subcategory::create($validated);

                $this->logs('Sub Category ' . $validated['name'] . ' was created');
            });

            return redirect()->route('subcategories')->with('success', 'Sub Category created successfully');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('subcategories')->with('error', 'Something went wrong');
        }
    }

    public function update(SubcategoryRequest $subcategoryRequest, Subcategory $subcategory)
    {
        $validated = $subcategoryRequest->validated();

        try {
            DB::transaction(function () use ($validated, $subcategory) {
                $subcategory->update($validated);

                $this->logs('Sub Category ' . $validated['name'] . ' was updated');
            });

            return redirect()->route('subcategories')->with('success', 'Sub Category updated successfully');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('subcategories')->with('error', 'Something went wrong');
        }
    }

    public function destroy(Subcategory $subcategory)
    {
        try {
            DB::transaction(function () use ($subcategory) {
                $subcategory->delete();

                $this->logs('Sub Category ' . $subcategory->name . ' was deleted');
            });

            return redirect()->route('subcategories')->with('success', 'Sub Category deleted successfully');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('subcategories')->with('error', 'Something went wrong');
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
