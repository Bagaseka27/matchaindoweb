<?php

use App\Http\Controllers\AuthWebController;
use App\Http\Controllers\CabangController;
use App\Http\Controllers\CupStockController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JenisCupController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Booting Awal
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => view('booting', [
    'redirect_url' => route('login.form'),
    'message'      => 'Memuat Sistem Matcha Indonesia...',
]))->name('booting.awal');

/*
|--------------------------------------------------------------------------
| Auth (Guest)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthWebController::class, 'showLoginForm'])->name('login.form');
    Route::post('/login', [AuthWebController::class, 'login'])->name('login.post');
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Owner yang sudah login)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Booting transisi & dashboard
    Route::get('/booting-dashboard', fn () => view('booting', [
        'redirect_url' => route('dashboard'),
        'message'      => 'Menyiapkan Dashboard Konsolidasi...',
    ]))->name('booting.dashboard');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/transaksi', [CupStockController::class, 'transactions'])->name('transaksi.index');
    Route::post('/logout', [AuthWebController::class, 'logout'])->name('logout');

    // Data master
    Route::resource('menus', MenuController::class)->except('show');
    Route::resource('kategori', KategoriController::class)->except('show');
    Route::resource('jenis-cup', JenisCupController::class)->except('show');

    // Cabang & shift
    Route::resource('cabang', CabangController::class);

    Route::prefix('cabang/{id}/shift')->name('cabang.shift')->group(function () {
        Route::get('/', [CabangController::class, 'shift']);
        Route::post('/', [CabangController::class, 'storeShift'])->name('.store');
    });

    Route::put('/shift/{id}', [CabangController::class, 'updateShift'])->name('shift.update');
    Route::delete('/shift/{id}', [CabangController::class, 'deleteShift'])->name('shift.delete');

    // Kelola Shift terpisah
    Route::get('/shift', [CabangController::class, 'indexShift'])
        ->name('shift.index');

    Route::post('/shift', [CabangController::class, 'storeShiftGlobal'])
        ->name('shift.store');

    Route::put('/shift/{id}', [CabangController::class, 'updateShift'])
        ->name('shift.update');

    Route::delete('/shift/{id}', [CabangController::class, 'deleteShift'])
        ->name('shift.delete');

    // User
    Route::resource('user', UserController::class);

    // Manajemen stok terpusat
    Route::get('/stok-cup', [CupStockController::class, 'index'])->name('stok-cup.index');
    Route::post('/stok-cup/restock', [CupStockController::class, 'restock'])->name('stok-cup.restock');

    Route::prefix('stok')->name('stok.')->group(function () {
        Route::get('/', [StokController::class, 'index'])->name('index');
        Route::get('/create', [StokController::class, 'create'])->name('create');
        Route::post('/', [StokController::class, 'store'])->name('store');

        Route::get('/{cabang}/{bahan}/edit', [StokController::class, 'edit'])->name('edit');
        Route::put('/{cabang}/{bahan}', [StokController::class, 'update'])->name('update');
        Route::delete('/{cabang}/{bahan}', [StokController::class, 'destroy'])->name('destroy');
    })->whereNumber(['cabang', 'bahan']);

    // Jadwal kerja barista
    Route::post('/jadwal-shift', [CabangController::class, 'storeJadwalShift'])
        ->name('jadwal-shift.store');

    Route::put('/jadwal-shift/{id}', [CabangController::class, 'updateJadwalShift'])
        ->name('jadwal-shift.update');

    Route::delete('/jadwal-shift/{id}', [CabangController::class, 'deleteJadwalShift'])
        ->name('jadwal-shift.delete');
});