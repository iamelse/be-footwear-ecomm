<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/migrate', function () {
    Artisan::call('migrate:fresh --seed');
    
    return 'Migrations have been reset and seeded!';
});