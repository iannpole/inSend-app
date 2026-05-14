<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminRecipeController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminUserController;

Route::get('/', function () {
    return view('admin/login');
});

// Admin Auth Routes
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login.form');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// Admin Protected Routes
Route::middleware(['web', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Products
    Route::resource('products', AdminProductController::class)->except(['show']);
    
    // Recipes
    Route::resource('recipes', AdminRecipeController::class)->except(['show']);
    
    // Orders
    Route::resource('orders', AdminOrderController::class)->only(['index', 'show']);
    Route::put('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update_status');
    
    // Users
    Route::resource('users', AdminUserController::class)->except(['create', 'store', 'show']);
});
