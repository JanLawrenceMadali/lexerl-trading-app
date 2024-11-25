<?php

use App\Http\Controllers\BackupController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CollectibleController;
use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\SupplierController;
use App\Models\ActivityLog;
use App\Models\Inventory;
use App\Models\Sale;

Route::get('/', function () {
    return redirect(route('login'));
});

Route::get('/dashboard', function () {
    $sale = Sale::all();
    $total_sale = $sale->sum('total_amount');
    $total_purchase = Inventory::sum('amount');
    $total_collectible = $sale->where('status_id', 2)->sum('total_amount');
    $activity_logs = ActivityLog::with('users')->latest()->get();

    return inertia('Dashboard', [
        'total_sale' => $total_sale,
        'total_purchase' => $total_purchase,
        'total_collectible' => $total_collectible,
        'activity_logs' => $activity_logs
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Search
    Route::inertia('/search', 'Search')->name('search');

    // Purchase In x Inventory
    Route::prefix('purchase-in')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('purchase-in');
        Route::post('/store', [InventoryController::class, 'store'])->name('purchase-in.store');
        Route::patch('/update/{inventory}', [InventoryController::class, 'update'])->name('purchase-in.update');
        Route::delete('/destroy/{inventory}', [InventoryController::class, 'destroy'])->name('purchase-in.destroy');
        Route::get('/export', [InventoryController::class, 'export'])->name('purchase-in.export');
    });

    // Sales
    Route::prefix('sales')->group(function () {
        Route::get('/', [SalesController::class, 'index'])->name('sales');
        Route::post('/store', [SalesController::class, 'store'])->name('sales.store');
        Route::patch('/update/{sale}', [SalesController::class, 'update'])->name('sales.update');
        Route::delete('/destroy/{sale}', [SalesController::class, 'destroy'])->name('sales.destroy');
        Route::get('/export', [SalesController::class, 'export'])->name('sales.export');
    });

    // Collectibles
    Route::prefix('collectibles')->group(function () {
        Route::get('/', [CollectibleController::class, 'index'])->name('collectibles');
        Route::post('/update', [CollectibleController::class, 'update'])->name('collectibles.update');
        Route::get('/export', [CollectibleController::class, 'export'])->name('collectibles.export');
    });

    // Reports
    Route::get('/activity_logs', [ReportController::class, 'activity_logs'])->name('activity_logs');
    Route::get('/activity_logs/export', [ReportController::class, 'activity_logs_export'])->name('activity_logs.export');
    Route::get('/current_inventory', [ReportController::class, 'current_inventory'])->name('current_inventory');
    Route::get('/current_inventory/export', [ReportController::class, 'current_inventory_export'])->name('current_inventory.export');
    Route::get('/sale_logs', [ReportController::class, 'sale_logs'])->name('sale_logs');

    // Users
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('users');
        Route::post('/store', [UserController::class, 'store'])->name('users.store');
        Route::patch('/update/{user}', [UserController::class, 'update'])->name('user.update');
        Route::delete('/destroy/{user}', [UserController::class, 'destroy'])->name('user.destroy');
    });
    // Supplier
    Route::prefix('suppliers')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('suppliers');
        Route::post('/store', [SupplierController::class, 'store'])->name('supplier.store');
        Route::patch('/update/{supplier}', [SupplierController::class, 'update'])->name('supplier.update');
        Route::delete('/destroy/{supplier}', [SupplierController::class, 'destroy'])->name('supplier.destroy');
    });
    // Customer
    Route::prefix('customers')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('customers');
        Route::post('/store', [CustomerController::class, 'store'])->name('customer.store');
        Route::patch('/update/{customer}', [CustomerController::class, 'update'])->name('customer.update');
        Route::delete('/destroy/{customer}', [CustomerController::class, 'destroy'])->name('customer.destroy');
    });
    // Category
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('categories');
        Route::post('/store', [CategoryController::class, 'store'])->name('categories.store');
        Route::patch('/update/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/destroy/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });
    // Subcategory
    Route::prefix('subcategories')->group(function () {
        Route::get('/', [SubCategoryController::class, 'index'])->name('subcategories');
        Route::post('/store', [SubCategoryController::class, 'store'])->name('subcategory.store');
        // Route::patch('/update/{subcategory}', [SubcategoryController::class, 'update'])->name('subcategory.update');
        // Route::delete('/destroy/{subcategory}', [SubcategoryController::class, 'destroy'])->name('subcategory.destroy');
    });

    // Backup
    Route::prefix('backup')->group(function () {
        Route::get('/', [BackupController::class, 'index'])->name('backup');
        Route::post('/manual', [BackupController::class, 'manualBackup'])->name('backup.manual');
        Route::post('/download', [BackupController::class, 'download'])->name('backup.download');
        Route::delete('/clean-all', [BackupController::class, 'cleanAll'])->name('backup.clean-all');
        Route::post('/restore', [BackupController::class, 'restore'])->name('backup.restore');
    });
});

require __DIR__ . '/auth.php';
