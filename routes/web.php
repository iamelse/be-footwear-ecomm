<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/migrate', function () {
    // Run the migration and seed the database
    Artisan::call('migrate:fresh --seed');
    
    // Generate JWT secret
    Artisan::call('jwt:secret');
    
    return 'Migrations have been reset, seeded, and JWT secret generated!';
});