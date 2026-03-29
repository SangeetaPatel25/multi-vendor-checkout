<?php

use App\Http\Controllers\AdminController;

Route::middleware(['auth:sanctum', 'can:admin.access'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/order/{id}', [AdminController::class, 'showOrder'])->name('admin.order.show');
});

Route::middleware(['auth:sanctum', 'throttle:60,1'])->prefix('admin')->group(function () {
    Route::get('/orders', [AdminController::class, 'viewAllOrders'])->middleware('can:admin.view-orders');
    Route::get('/orders/{orderId}', [AdminController::class, 'viewOrderDetails'])->middleware('can:admin.view-order');
    Route::get('/vendors', [AdminController::class, 'getVendors'])->middleware('can:admin.view-vendors');
    Route::get('/customers', [AdminController::class, 'getCustomers'])->middleware('can:admin.view-customers');
    Route::get('/stats', [AdminController::class, 'getOrderStats'])->middleware('can:admin.view-stats');
    Route::get('/cart-items', [AdminController::class, 'getActiveCartItems'])->middleware('can:admin.view-cart-items');
    Route::get('/product-buyers', [AdminController::class, 'getProductBuyers'])->middleware('can:admin.view-product-buyers');
    Route::get('/products', [AdminController::class, 'getProducts'])->middleware('can:admin.view-products');
    Route::post('/products', [AdminController::class, 'storeProduct'])->middleware('can:admin.create-products');
});
