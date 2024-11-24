<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::latest()->get();
        return inertia('Settings/Category/Index', [
            'categories' => $categories,
        ]);
    }
}
