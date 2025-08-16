<?php

use App\Http\Controllers\AbsenController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FaceController;
use App\Http\Controllers\SubjectController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/register', [FaceController::class, 'registerForm']);
    Route::post('/register', [FaceController::class, 'register']);
    Route::resource('subjects', SubjectController::class);
    Route::resource('/', AbsenController::class)->name('index', 'beranda');
    Route::get('/absen/{subject}', [FaceController::class, 'absenForm'])->name('absen.show');
    Route::post('/absen', [FaceController::class, 'absen'])->name('absen');
    Route::post('/absen/status', [AbsenController::class, 'updateStatus'])->name('absen.updateStatus');

    // route logout
});
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.process');
});
