<?php

Route::view('/', 'home')->name('home');
Route::view('/login', 'auth.login')->name('login');
Route::view('/products', 'products.index')->name('web.products');
Route::view('/cart', 'cart.index')->name('web.cart');
Route::view('/account/orders', 'account.orders')->name('web.account.orders');
Route::view('/admin/dashboard', 'admin.dashboard')->name('web.admin.dashboard');
Route::view('/checkout/payment', 'checkout.payment')->name('web.checkout.payment');
Route::view('/checkout/success', 'checkout.success')->name('web.checkout.success');
