<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthWebController;
use App\Http\Controllers\DashboardController;

// Booting Awal
Route::get('/', function () {
    return view('booting', ['redirect_url' => route('login.form'), 'message' => 'Memuat Sistem Matcha Indonesia...']);
})->name('booting.awal');

// Auth Routes (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthWebController::class, 'showLoginForm'])->name('login.form');
    Route::post('/login', [AuthWebController::class, 'login'])->name('login.post');
});

// Protected Routes (Hanya untuk Owner yang sudah login)
Route::middleware('auth')->group(function () {
    // Booting Transisi ke Dashboard
    Route::get('/booting-dashboard', function () {
        return view('booting', ['redirect_url' => route('dashboard'), 'message' => 'Menyiapkan Dashboard Konsolidasi...']);
    })->name('booting.dashboard');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthWebController::class, 'logout'])->name('logout');
});