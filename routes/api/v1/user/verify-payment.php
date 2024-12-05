<?php

use App\Http\Controllers\API\User\VerifyPaymentController;
use Illuminate\Support\Facades\Route;

Route::post('/verify-payment', [VerifyPaymentController::class, 'verifyPayment'])->name('verify.payment');