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

    Route::get('/purchaseIn', [PurchaseInController::class, 'index'])->name('purchaseIn');
    // Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions');
    // Route::post('/transactions/store', [TransactionController::class, 'store'])->name('transactions.store');
    // Route::get('/sales', [SalesController::class, 'index'])->name('sales');

    // Settings
    Route::inertia('/settings', 'Settings/Index')->name('settings');
    Route::get('/users', [UserController::class, 'index'])->name('settings.users');
});

require __DIR__ . '/auth.php';
