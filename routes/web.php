<?php

use App\Http\Controllers\Api\EventController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;
use App\Livewire\Events\QuickRegister;
use App\Http\Controllers\Api\GameEventController;
use App\Http\Controllers\Controller;

Route::get('/image/{file_path}', [FileController::class, 'image'])
    ->where('file_path', '.*')
    ->name('public_image');

Route::middleware(['auth:sanctum', 'auth:web'])->get('/document/{file_path}', [FileController::class, 'document'])
    ->where('file_path', '.*')
    ->name('file_document');

Route::middleware(['auth:sanctum'])->get('/file-private/{document_id}/{file_id}', [EventController::class, 'downloadDocumentFile'])
    ->name('private_file');

Route::get('/event/quick-register', QuickRegister::class)->name('events.quick-register');
Route::get("/", [Controller::class, 'index'])->name('home');
Route::get('/admin/play/{id}', [GameEventController::class, 'show'])->name('game.play');

Route::middleware(['auth:web'])->prefix('/event-game')->group(function () {
    Route::get('/gifts/{gameId}', [GameEventController::class, 'getGiftsEventGame']);
    Route::get('/history-gifts/{gameId}', [GameEventController::class, 'getHistoryGifts']);
    Route::get('/users/{gameId}', [GameEventController::class, 'getUsers']);
    Route::post('/spin/{gameId}', [GameEventController::class, 'spin']);
});
