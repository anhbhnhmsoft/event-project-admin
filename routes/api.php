<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\EventPollController;
use App\Http\Controllers\Api\GameEventController;
use App\Http\Controllers\Api\MembershipController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrganizerController;
use App\Http\Controllers\Api\ProvinceController;
use App\Http\Controllers\Api\TransactionController;
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
            ->name('verification.verify');
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
        Route::get('/gift', [GameEventController::class, 'listUserGift']);
        Route::get('/link-support',[AuthController::class, 'supportLink']);
    });

    Route::prefix('/event')->group(function () {
        Route::get('/', [EventController::class, 'list']);
        Route::post('/history', [EventController::class, 'eventUserHistory']);
        Route::post('/history_register', [EventController::class, 'createEventUserHistory']);
        Route::post('/document/register', [TransactionController::class, 'registerComment']);
        Route::post('/comment', [EventController::class, 'createEventComment']);
        Route::get('/list-comment', [EventController::class, 'listComment']);
        Route::get('/{id}', [EventController::class, 'show']);
        Route::get('/{id}/area', [EventController::class, 'showArea']);
        Route::get('/{id}/area/{areaId}', [EventController::class, 'showSeat']);
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
        Route::get('/unread', [NotificationController::class, 'getUnReadCount']);
        Route::post('/push-token', [NotificationController::class, 'storePushToken']);
        Route::post('/read', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
    });
    Route::prefix('/schedule')->group(function () {
        Route::get('/list', [EventController::class, 'listDocument']);
        Route::post('/document/register', [TransactionController::class, 'registerDocument']);
        Route::get('/document/{id}', [EventController::class, 'getDetailScheduleDocument']);
        Route::get('/{id}', [EventController::class, 'getDetailSchedule']);
    });
});


Route::prefix('common')->middleware('set-locale')->group(function () {
    Route::get('/organizers', [OrganizerController::class, 'getOrganizers']);
    Route::get('/province', [ProvinceController::class, 'getProvinces']);
    Route::get('/district/{code}', [ProvinceController::class, 'getDistricts']);
    Route::get('/ward/{code}', [ProvinceController::class, 'getWards']);
});

Route::post('/webhook/payos', [WebhookCassoController::class, 'handle']);
Route::post('/webhook/event-seat-payment', [EventController::class, 'handleSeatPaymentWebhook']);
