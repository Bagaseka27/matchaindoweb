<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MenuController;

// Endpoint publik (Tidak perlu token)
Route::post('/login', [AuthController::class, 'login']);

// Endpoint terproteksi (Wajib kirim token di Header: Authorization Bearer <token>)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user-profile', function (Illuminate\Http\Request $request) {
        return response()->json(['status' => true, 'data' => $request->user()]);
    });

    // Menu (read-only untuk barista)
    Route::get('/menus', [MenuController::class, 'index']);
    Route::get('/menus/{menu}', [MenuController::class, 'show']);

    // Nanti endpoint transaksi kasir ditaruh di sini
});