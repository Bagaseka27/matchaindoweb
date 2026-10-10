<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\StokController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Endpoint Publik (tanpa token)
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Endpoint Terproteksi
| Wajib kirim header: Authorization: Bearer <token>
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Auth & profil
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user-profile', fn (Request $request) => response()->json([
        'status' => true,
        'data'   => $request->user(),
    ]));

    // Menu (read-only untuk barista)
    Route::get('/menus', [MenuController::class, 'index']);
    Route::get('/menus/{menu}', [MenuController::class, 'show']);

    // Stok
    Route::get('/stok', [StokController::class, 'index']);

    // Nanti endpoint transaksi kasir ditaruh di sini
});