<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VendorAuthController;

// Signup
Route::post('/vendor/signup', [VendorAuthController::class, 'signup']);
Route::post('/vendor/signup/send-otp', [VendorAuthController::class, 'signupSendOtp']);
Route::post('/vendor/signup/resend-otp', [VendorAuthController::class, 'signupResendOtp']);
Route::post('/vendor/signup/verify-otp', [VendorAuthController::class, 'signupVerifyOtp']);

// Forgot Password
Route::post('/vendor/forgot-password/send-otp', [VendorAuthController::class, 'forgotPasswordSendOtp']);
Route::post('/vendor/forgot-password/resend-otp', [VendorAuthController::class, 'forgotPasswordResendOtp']);
Route::post('/vendor/forgot-password/verify-otp', [VendorAuthController::class, 'forgotPasswordVerifyOtp']);
Route::post('/vendor/forgot-password/reset-password', [VendorAuthController::class, 'forgotPasswordReset']);

// Login
Route::post('/vendor/login', [VendorAuthController::class, 'login']);
Route::post('/vendor/login/send-otp', [VendorAuthController::class, 'loginSendOtp']);
Route::post('/vendor/login/resend-otp', [VendorAuthController::class, 'loginResendOtp']);
Route::post('/vendor/login/verify-otp', [VendorAuthController::class, 'loginVerifyOtp']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/vendor/logout', [VendorAuthController::class, 'logout']);
});
