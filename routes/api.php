<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

// Endpoint publik (Tidak perlu token)
Route::post('/login', [AuthController::class, 'login']);

// Endpoint terproteksi (Wajib kirim token di Header: Authorization Bearer <token>)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Nanti endpoint transaksi kasir, ambil menu, dll ditaruh di dalam sini
    Route::get('/user-profile', function (Illuminate\Http\Request $request) {
        return response()->json(['status' => true, 'data' => $request->user()]);
    });
});