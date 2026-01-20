<?php

use App\Http\Controllers\Api\CustomerAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VendorAuthController;

// Vendor Signup
Route::post('/vendor/signup', [VendorAuthController::class, 'signup']);
Route::post('/vendor/signup/send-otp', [VendorAuthController::class, 'signupSendOtp']);
Route::post('/vendor/signup/resend-otp', [VendorAuthController::class, 'signupResendOtp']);
Route::post('/vendor/signup/verify-otp', [VendorAuthController::class, 'signupVerifyOtp']);

// Vendor Forgot Password
Route::post('/vendor/forgot-password/send-otp', [VendorAuthController::class, 'forgotPasswordSendOtp']);
Route::post('/vendor/forgot-password/resend-otp', [VendorAuthController::class, 'forgotPasswordResendOtp']);
Route::post('/vendor/forgot-password/verify-otp', [VendorAuthController::class, 'forgotPasswordVerifyOtp']);
Route::post('/vendor/forgot-password/reset-password', [VendorAuthController::class, 'forgotPasswordReset']);

// Vendor Login
Route::post('/vendor/login', [VendorAuthController::class, 'login']);
Route::post('/vendor/login/send-otp', [VendorAuthController::class, 'loginSendOtp']);
Route::post('/vendor/login/resend-otp', [VendorAuthController::class, 'loginResendOtp']);
Route::post('/vendor/login/verify-otp', [VendorAuthController::class, 'loginVerifyOtp']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/vendor/logout', [VendorAuthController::class, 'logout']);
});

// Customer Signup
Route::post('/customer/signup', [CustomerAuthController::class, 'signup']);
Route::post('/customer/signup/send-otp', [CustomerAuthController::class, 'signupSendOtp']);
Route::post('/customer/signup/resend-otp', [CustomerAuthController::class, 'signupResendOtp']);
Route::post('/customer/signup/verify-otp', [CustomerAuthController::class, 'signupVerifyOtp']);

// Customer Forgot Password
Route::post('/customer/forgot-password/send-otp', [CustomerAuthController::class, 'forgotPasswordSendOtp']);
Route::post('/customer/forgot-password/resend-otp', [CustomerAuthController::class, 'forgotPasswordResendOtp']);
Route::post('/customer/forgot-password/verify-otp', [CustomerAuthController::class, 'forgotPasswordVerifyOtp']);
Route::post('/customer/forgot-password/reset-password', [CustomerAuthController::class, 'forgotPasswordReset']);


