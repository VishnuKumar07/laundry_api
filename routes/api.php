<?php

use App\Http\Controllers\Api\CustomerAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VendorAuthController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\CustomerHomeController;
use App\Http\Controllers\Api\CustomerFavoriteController;
use App\Http\Controllers\Api\CustomerVendorRatingController;
use App\Http\Controllers\Api\VendorWorkingHourController;

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

// Customer Login
Route::post('/customer/login', [CustomerAuthController::class, 'login']);
Route::post('/customer/login/send-otp', [CustomerAuthController::class, 'loginSendOtp']);
Route::post('/customer/login/resend-otp', [CustomerAuthController::class, 'loginResendOtp']);
Route::post('/customer/login/verify-otp', [CustomerAuthController::class, 'loginVerifyOtp']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/services', [ServiceController::class, 'index']);

    Route::get('/customer/home/vendors', [CustomerHomeController::class, 'vendors']);
    Route::post('/customer/favorites', [CustomerFavoriteController::class, 'store']);
    Route::delete('/customer/favorites', [CustomerFavoriteController::class, 'destroy']);
    Route::post('/customer/vendor/addratings', [CustomerVendorRatingController::class, 'store']);
    Route::post('/customer/vendor/rating/list', [CustomerVendorRatingController::class, 'ratingList']);
    Route::post('/customer/logout', [CustomerAuthController::class, 'logout']);

    Route::post('/vendor/add-update/working-hours',[VendorWorkingHourController::class, 'store']);
    Route::get('/vendor/get/working-hours',[VendorWorkingHourController::class, 'index']);
    Route::post('/vendor/logout', [VendorAuthController::class, 'logout']);

});
