<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProductController;

// Public routes
Route::get('/products', [ProductController::class, 'index'])->name('products.index');

// Authentication routes (with rate limiting)
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes with Sanctum
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Cart routes
    Route::post('/cart/add', [CartController::class, 'addToCart']);
    Route::get('/cart', [CartController::class, 'viewCart']);
    Route::put('/cart/update', [CartController::class, 'updateQuantity']);
    Route::delete('/cart/remove', [CartController::class, 'removeFromCart']);

    // Checkout routes
    Route::post('/checkout', [CheckoutController::class, 'checkout']);
    Route::post('/payment/success', [CheckoutController::class, 'paymentSuccess']);
    Route::get('/orders', [CheckoutController::class, 'orderHistory']);
});
