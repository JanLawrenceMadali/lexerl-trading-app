<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseInController;
use App\Http\Controllers\Setting\UserController;
use App\Http\Controllers\Transaction\SalesController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect(route('login'));
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Search
    Route::inertia('/search', 'Search')->name('search');

    Route::prefix('purchase-in')->group(function () {
        Route::get('/', [PurchaseInController::class, 'index'])->name('purchase-in');
        Route::get('/create', [PurchaseInController::class, 'create'])->name('purchase-in.create');
        Route::post('/store', [PurchaseInController::class, 'store'])->name('purchase-in.store');
        Route::patch('/update/{purchase}', [PurchaseInController::class, 'update'])->name('purchase-in.update');
        Route::delete('/destroy/{purchase}', [PurchaseInController::class, 'destroy'])->name('purchase-in.destroy');
    });


    // Settings
    Route::inertia('/settings', 'Settings/Index')->name('settings');
    Route::get('/users', [UserController::class, 'index'])->name('settings.users');
});

require __DIR__ . '/auth.php';
