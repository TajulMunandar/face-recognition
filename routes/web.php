<?php

use App\Http\Controllers\FaceController;
use Illuminate\Support\Facades\Route;


Route::get('/register', [FaceController::class, 'registerForm']);
Route::get('/absen', [FaceController::class, 'absenForm']);
Route::post('/register', [FaceController::class, 'register']);
Route::post('/absen', [FaceController::class, 'absen']);
