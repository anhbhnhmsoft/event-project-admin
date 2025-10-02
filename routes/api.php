<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\GameEventController;
use App\Http\Controllers\Api\MembershipController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrganizerController;
use App\Http\Controllers\Api\ProvinceController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\WebhookCassoController;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->middleware(['set-locale', 'throttle:5,1'])->group(function () {
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

Route::middleware(['set-locale', 'auth:sanctum'])->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'getUserInfo']);
        Route::post('/edit-info', [AuthController::class, 'edit']);
        Route::post('/edit-avatar', [AuthController::class, 'editAvatar']);
        Route::delete('/delete-avatar', [AuthController::class, 'deleteAvatar']);
        Route::post('/set-lang', [AuthController::class, 'setLang']);
    });

    Route::prefix('/event')->group(function () {
        Route::get('/', [EventController::class, 'list']);
        Route::post('/history', [EventController::class, 'eventUserHistory']);
        Route::post('/history_register', [EventController::class, 'createEventUserHistory']);
        Route::post('/comment', [EventController::class, 'createEventComment']);
        Route::get('/list-comment', [EventController::class, 'listComment']);
        Route::get('/{id}', [EventController::class, 'show']);
    });

    Route::prefix('/membership')->group(function () {
        Route::get('/', [MembershipController::class, 'listMembership']);
        Route::get('/account', [MembershipController::class, 'listAccountMembership']);
        Route::post('/register', [MembershipController::class, 'membershipRegister']);
        Route::get('/{id}', [MembershipController::class, 'show']);
    });

    Route::prefix('/transaction')->group(function () {
        Route::get('/check-payment/{id}', [TransactionController::class, 'checkPayment']);
        Route::get('/{id}', [TransactionController::class, 'show']);
    });
    Route::prefix('/notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('/', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::post('/push-token', [NotificationController::class, 'storePushToken']);
        Route::post('/send', [NotificationController::class, 'sendNotification']);
    });
    Route::prefix('/notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('/', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::post('/push-token', [NotificationController::class, 'storePushToken']);
        Route::post('/send', [NotificationController::class, 'sendNotification']);
    });
    Route::prefix('/schedule')->group(function () {
        Route::get('/document/{id}', [EventController::class, 'getDetailScheduleDocument']);
        Route::get('/{id}', [EventController::class, 'getDetailSchedule']);
    });
});

Route::prefix('/event-game')->group(function () {
    Route::get('/gifts/{gameId}', [GameEventController::class, 'getGiftsEventGame']);
    Route::get('/history-gifts/{gameId}', [GameEventController::class, 'getHistoryGifts']);
    Route::post('/history-gifts/{gameId}', [GameEventController::class, 'insertHistoryGift']);
    Route::get('/users/{gameId}', [GameEventController::class, 'getUsers']);
});

Route::prefix('common')->middleware('set-locale')->group(function () {
    Route::get('/organizers', [OrganizerController::class, 'getOrganizers']);
    Route::get('/province', [ProvinceController::class, 'getProvinces']);
    Route::get('/district/{code}', [ProvinceController::class, 'getDistricts']);
    Route::get('/ward/{code}', [ProvinceController::class, 'getWards']);
});


Route::post('/webhook/payos', [WebhookCassoController::class, 'handle']);
