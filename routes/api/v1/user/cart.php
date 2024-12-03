<?php

use App\Http\Controllers\API\User\CartController;
use Illuminate\Support\Facades\Route;

Route::prefix('cart')->middleware('api.is.auth')->group(function () {
    Route::post('/add/{productId}', [CartController::class, 'store'])->name('cart.store');
    Route::get('/', [CartController::class, 'index'])->name('cart.index')->name('cart.index');
    Route::put('/{productId}/update', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/{productId}', [CartController::class, 'destroy'])->name('cart.destroy');
});