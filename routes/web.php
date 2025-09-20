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
    Route::get('/absen/{meeting}', [FaceController::class, 'absenForm'])->name('absen.show');
    Route::post('/absen', [FaceController::class, 'absen'])->name('absen');

    Route::get('/', [AbsenController::class, 'index'])->name('beranda');
    Route::post('/absen/status', [AbsenController::class, 'updateStatus'])->name('absen.updateStatus');
    Route::resource('subjects', SubjectController::class);
    // route logout
});

Route::get('/attendance/subject/{subject}/download', [AbsenController::class, 'downloadSubjectPdf'])
    ->name('attendance.subject.download');


Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.process');
});
