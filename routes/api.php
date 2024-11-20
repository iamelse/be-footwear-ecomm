<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('v1')->group(function () {
    Route::get('/', function () {
        return response()->json([
            'message' => 'Hello World!'
        ]);
    });
    include __DIR__.'/api/v1/auth.php';
    include __DIR__.'/api/v1/fe/shoe.php';
    Route::prefix('user')->group(function () {
        include __DIR__.'/api/v1/user/cart.php';
    });
});