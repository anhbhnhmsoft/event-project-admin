<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrganizerController;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
Route::post('/confirm-password', [AuthController::class, 'confirmPassword'])->middleware('throttle:5,1');
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed','throttle:6,1'])
    ->name('api.verification.verify');

Route::post('/email/verification-notification', [AuthController::class, 'resendVerify'])
    ->middleware('throttle:6,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'getUserInfo']);
});
Route::prefix('organizers')->middleware('set-locale')->group(function () {
    Route::get('/', [OrganizerController::class, 'getOrganizers']);
});