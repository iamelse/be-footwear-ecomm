<?php

use App\Http\Controllers\API\User\CheckoutController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.is.auth')->prefix('checkout')->group(function () {
    Route::post('/product', [CheckoutController::class, 'checkoutFromProduct'])->name('checkout.from.product');
    Route::post('/cart', [CheckoutController::class, 'checkoutFromCart'])->name('checkout.from.cart');
});