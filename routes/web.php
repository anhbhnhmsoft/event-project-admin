<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;
Route::get('/image/{file_path}', [FileController::class, 'image'])
    ->where('file_path', '.*')
    ->name('public_image');