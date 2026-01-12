<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VendorAuthController;

Route::post('/vendor/signup', [VendorAuthController::class, 'signup']);
Route::post('/vendor/send-otp', [VendorAuthController::class, 'sendOtp']);
Route::post('/vendor/resend-otp', [VendorAuthController::class, 'resendOtp']);
Route::post('/vendor/verify-otp', [VendorAuthController::class, 'verifyOtp']);
Route::post('/vendor/forgot-password/send-otp', [VendorAuthController::class, 'forgotPasswordSendOtp']);
Route::post('/vendor/forgot-password/resend-otp', [VendorAuthController::class, 'forgotPasswordResendOtp']);
Route::post('/vendor/forgot-password/verify-otp', [VendorAuthController::class, 'forgotPasswordVerifyOtp']);
Route::post('/vendor/forgot-password/reset-password', [VendorAuthController::class, 'forgotPasswordReset']);
Route::post('/vendor/login', [VendorAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/vendor/logout', [VendorAuthController::class, 'logout']);
});

