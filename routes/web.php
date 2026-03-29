<?php

Route::get('/', function () {
    return response()->json([
        'message' => 'Multi-Vendor Checkout API',
    ]);
});
