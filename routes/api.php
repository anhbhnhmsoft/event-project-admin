<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrganizerController;
use Illuminate\Support\Facades\Route;



Route::middleware('set-locale')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
    Route::post('/confirm-password', [AuthController::class, 'confirmPassword'])->middleware('throttle:5,1');
    Route::prefix('email')->group(function () {
        Route::get('/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
            ->middleware(['signed', 'throttle:6,1'])
            ->name('api.verification.verify');
        Route::post('/verification-notification', [AuthController::class, 'resendVerify'])
            ->middleware('throttle:6,1');
    });
});

Route::middleware(['set-locale','auth:sanctum'])->group(function () {
    Route::get('/user', [AuthController::class, 'getUserInfo']);
});

Route::prefix('common')->middleware('set-locale')->group(function () {
    Route::get('/organizers', [OrganizerController::class, 'getOrganizers']);
});
