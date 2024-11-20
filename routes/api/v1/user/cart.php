<?php

use App\Http\Controllers\API\User\CartController;
use Illuminate\Support\Facades\Route;

Route::prefix('cart')->middleware('api.is.auth')->group(function () {
    Route::post('/add/{productId}', [CartController::class, 'addToCart'])->name('cart.add');
    Route::get('/', [CartController::class, 'showCart'])->name('cart.index');
    //Route::delete('/cart/{cartItemId}', [CartController::class, 'removeItem'])->name('cart.remove');
    //Route::patch('/cart/{cartItemId}/update', [CartController::class, 'updateQuantity'])->name('cart.update');
});