<?php

use App\Http\Controllers\API\FE\ShoeController;
use Illuminate\Support\Facades\Route;

Route::prefix('shoe')->group(function () {
    Route::get('/', [ShoeController::class, 'index']);
    Route::get('/{slug}', [ShoeController::class, 'show']);
});