<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

// UI Routes (web-based, not API)
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/cart/view', [CartController::class, 'showCart'])->name('cart.view');
Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
Route::get('/admin/order/{id}', [AdminController::class, 'showOrder'])->name('admin.order.show');
Route::get('/login', function () { return view('auth.login'); })->name('login');
Route::post('/logout', function () { auth()->logout(); return redirect('/'); })->name('logout');

// Web routes for cart operations (redirect to API routes)
Route::post('/cart/add', function (Request $request) {
    $response = app()->call('App\Http\Controllers\CartController@addToCart', ['request' => $request]);
    return $response->getStatusCode() === 200
        ? redirect()->route('cart.view')->with('success', 'Product added to cart!')
        : redirect()->back()->with('error', 'Failed to add product to cart.');
})->name('cart.add');

Route::put('/cart/update', function (Request $request) {
    $response = app()->call('App\Http\Controllers\CartController@updateQuantity', ['request' => $request]);
    return redirect()->route('cart.view');
})->name('cart.update');

Route::delete('/cart/remove', function (Request $request) {
    $response = app()->call('App\Http\Controllers\CartController@removeFromCart', ['request' => $request]);
    return redirect()->route('cart.view');
})->name('cart.remove');

Route::post('/checkout', [CheckoutController::class, 'checkout'])->name('checkout');

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

    // Admin routes (AdminController handles role checking)
    Route::prefix('admin')->group(function () {
        Route::get('/orders', [AdminController::class, 'viewAllOrders']);
        Route::get('/orders/{orderId}', [AdminController::class, 'viewOrderDetails']);
        Route::get('/vendors', [AdminController::class, 'getVendors']);
        Route::get('/customers', [AdminController::class, 'getCustomers']);
        Route::get('/stats', [AdminController::class, 'getOrderStats']);
    });
});
