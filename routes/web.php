<?php

use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\EventPollController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;
use App\Livewire\Events\QuickRegister;
use App\Http\Controllers\Api\GameEventController;
use App\Livewire\SignupOrganizer;

Route::get('/image/{file_path}', [FileController::class, 'image'])
    ->where('file_path', '.*')
    ->name('public_image');

Route::middleware(['auth:sanctum', 'auth:web'])->get('/document/{file_path}', [FileController::class, 'document'])
    ->where('file_path', '.*')
    ->name('file_document');

Route::middleware(['auth:sanctum'])->get('/file-private/{document_id}/{file_id}', [EventController::class, 'downloadDocumentFile'])
    ->name('private_file');

Route::get('/event/quick-register', QuickRegister::class)->name('events.quick-register');
Route::get('/event/quick-checkin', \App\Livewire\Events\QuickCheckin::class)->name('events.quick-checkin');
Route::get("/", [EventController::class, 'index'])->name('home');
Route::get('/admin/play/{id}', [GameEventController::class, 'show'])->name('game.play');
Route::get('/survey/{idcode}', [EventPollController::class, 'show'])->name('event.poll.show');
Route::post('/survey/{idcode}', [EventPollController::class, 'submit'])->name('event.poll.submit');

Route::get('/signup', SignupOrganizer::class)->name('signup');
Route::get('/event-screen/{id}', [EventController::class, 'eventScreen'])->name('event.screen');

Route::middleware(['auth:web'])->prefix('/event-game')->group(function () {
    Route::get('/gifts/{gameId}', [GameEventController::class, 'getGiftsEventGame']);
    Route::get('/history-gifts/{gameId}', [GameEventController::class, 'getHistoryGifts']);
    Route::get('/users/{gameId}', [GameEventController::class, 'getUsers']);
    Route::post('/initiate-spin/{gameId}', [GameEventController::class, 'initiateSpin']);
    Route::post('/reveal-prize/{gameId}', [GameEventController::class, 'revealPrize']);
    Route::post('/initiate-spin-user/{gameId}', [GameEventController::class, 'initiateSpinUser']);
});

Route::get('lang/{locale}', [App\Http\Controllers\LanguageController::class, 'switch'])->name('lang.switch');

// Zalo Access Token Initialization Routes
Route::get('/zalo/redirect', [App\Http\Controllers\ZaloController::class, 'redirect'])->name('zalo.redirect');
Route::get('/zalo/callback', [App\Http\Controllers\ZaloController::class, 'callback'])->name('zalo.callback');

Route::get('/membership/purchase', \App\Livewire\MembershipPurchase::class)->name('membership.purchase');

