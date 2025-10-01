<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;
use App\Livewire\Events\QuickRegister;
use App\Http\Controllers\Api\GameEventController;

Route::get('/image/{file_path}', [FileController::class, 'image'])
    ->where('file_path', '.*')
    ->name('public_image');

Route::get('/event/quick-register', QuickRegister::class)->name('events.quick-register');
Route::get("/")->name('home');
Route::get('/admin/play/{id}', [GameEventController::class, 'show'])->name('game.play');
