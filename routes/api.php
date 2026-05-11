<?php

use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

Route::post('/open-file', [FileController::class, 'openFile'])->name('api.open-file');
