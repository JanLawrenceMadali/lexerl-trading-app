<?php

use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\Setting\UserController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\SupplierController;
use App\Models\ActivityLog;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect(route('login'));
});

Route::get('/dashboard', function () {
    $users = User::all();
    $sales = Sale::all();

    return inertia('Dashboard', [
        'users' => $users->count(),
        'sales' => $sales->sum('total_amount')
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Search
    Route::inertia('/search', 'Search')->name('search');

    // Purchase In x Inventory
    Route::prefix('purchase-in')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('purchase-in');
        Route::post('/store', [InventoryController::class, 'store'])->name('purchase-in.store');
        Route::patch('/update/{purchase}', [InventoryController::class, 'update'])->name('purchase-in.update');
        Route::delete('/destroy/{purchase}', [InventoryController::class, 'destroy'])->name('purchase-in.destroy');
    });

    // Sales
    Route::prefix('sales')->group(function () {
        Route::get('/', [SalesController::class, 'index'])->name('sales');
        Route::post('/store', [SalesController::class, 'store'])->name('sales.store');
        Route::patch('/update/{sale}', [SalesController::class, 'update'])->name('sales.update');
        Route::delete('/destroy/{sale}', [SalesController::class, 'destroy'])->name('sales.destroy');
    });

    // Collectibles
    // Route::prefix('collectibles')->group(function () {
    //     Route::get('/', [CollectibleController::class, 'index'])->name('sales');
    //     Route::post('/store', [CollectibleController::class, 'store'])->name('sales.store');
    //     Route::patch('/update/{sale}', [CollectibleController::class, 'update'])->name('sales.update');
    //     Route::delete('/destroy/{sale}', [CollectibleController::class, 'destroy'])->name('sales.destroy');
    // });

    // Reports
    Route::get('/activity_logs', function () {
        return Inertia::render('Reports/ActivityLogs/Index', [
            'logs' => ActivityLog::with('users')->latest()->get()
        ]);
    })->name('activity_logs');

    Route::get('/inventory', function () {
        $inventories = DB::table('inventories')
            ->join('units', 'inventories.unit_id', '=', 'units.id')
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('subcategories', 'products.subcategory_id', '=', 'subcategories.id')
            ->select(
                'products.id as product_id',
                'units.abbreviation as unit',
                'categories.name as category_name',
                'subcategories.name as subcategory_name',
                DB::raw('SUM(inventories.quantity) as total_quantity')
            )
            ->groupBy('products.id', 'units.abbreviation', 'categories.name', 'subcategories.name')
            ->having('total_quantity', '>', 0)
            ->get()
            ->map(function ($item) {
                return [
                    'category_name' => $item->category_name,
                    'subcategory_name' => $item->subcategory_name,
                    'unit' => $item->unit,
                    'quantity' => $item->total_quantity . ' left'
                ];
            })
            ->filter(function ($item) {
                return intval($item['quantity']) > 0;
            });


        return Inertia::render('Reports/Inventory/Index', [
            'inventories' => $inventories
        ]);
    })->name('inventory');

    // Settings
    Route::inertia('/settings', 'Settings/Index')->name('settings');
    // Supplier
    Route::prefix('suppliers')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('settings.suppliers');
        Route::post('/store', [SupplierController::class, 'store'])->name('supplier.store');
        // Route::patch('/update/{supplier}', [SupplierController::class, 'update'])->name('supplier.update');
        // Route::delete('/destroy/{supplier}', [SupplierController::class, 'destroy'])->name('supplier.destroy');
    });
    // Customer
    Route::prefix('customer')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('settings.customer');
        Route::post('/store', [CustomerController::class, 'store'])->name('customer.store');
        // Route::patch('/update/{customer}', [SupplierController::class, 'update'])->name('customer.update');
        // Route::delete('/destroy/{customer}', [SupplierController::class, 'destroy'])->name('customer.destroy');
    });
    // Subcategory
    Route::prefix('subcategories')->group(function () {
        Route::get('/', [SubCategoryController::class, 'index'])->name('settings.subcategories');
        Route::post('/store', [SubcategoryController::class, 'store'])->name('subcategory.store');
        // Route::patch('/update/{subcategory}', [SubcategoryController::class, 'update'])->name('subcategory.update');
        // Route::delete('/destroy/{subcategory}', [SubcategoryController::class, 'destroy'])->name('subcategory.destroy');
    });
    Route::get('/users', [UserController::class, 'index'])->name('settings.users');
});

require __DIR__ . '/auth.php';
