<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VendorAuthController;

Route::post('/vendor/signup', [VendorAuthController::class, 'signup']);
Route::post('/vendor/send-otp', [VendorAuthController::class, 'sendOtp']);
Route::post('/vendor/resend-otp', [VendorAuthController::class, 'resendOtp']);
Route::post('/vendor/verify-otp', [VendorAuthController::class, 'verifyOtp']);


