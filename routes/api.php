<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\OrganizerController;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->middleware(['set-locale','throttle:5,1'])->group(function (){
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/confirm-password', [AuthController::class, 'confirmPassword']);
});

Route::middleware('set-locale')->group(function () {
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

    Route::prefix('/event')->group(function () {
        Route::post('/', [EventController::class, 'getEvents']);
        Route::get('/{id}', [EventController::class, 'getEventInfo']);
    });
});

Route::prefix('common')->middleware('set-locale')->group(function () {
    Route::get('/organizers', [OrganizerController::class, 'getOrganizers']);
});
