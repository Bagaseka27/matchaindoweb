<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            // Order ID unik untuk Midtrans (contoh: MTCH-1-20261113-00001)
            $table->string('order_id', 50)->nullable()->unique()->after('id_transaksi');

            // Snap Token dari Midtrans, dikirim ke mobile agar bisa buka payment page
            $table->string('snap_token', 255)->nullable()->after('kembalian');

            // Status pembayaran Midtrans
            $table->enum('payment_status', [
                'pending',    // Menunggu pembayaran
                'paid',       // Sudah dibayar (settlement / capture)
                'expired',    // Kedaluwarsa
                'cancelled',  // Dibatalkan
                'failed',     // Gagal
            ])->default('pending')->after('snap_token');

            // Tipe pembayaran dari Midtrans (gopay, shopeepay, qris, bank_transfer, dll.)
            $table->string('midtrans_payment_type', 50)->nullable()->after('payment_status');

            // Waktu pembayaran diterima
            $table->dateTime('paid_at')->nullable()->after('midtrans_payment_type');
        });

        // Update semua transaksi lama yang tunai → payment_status = 'paid'
        DB::table('transaksi')
            ->where('metode_bayar', 'tunai')
            ->update(['payment_status' => 'paid']);

        // Update transaksi QRIS lama → payment_status = 'paid' (asumsi sudah lunas)
        DB::table('transaksi')
            ->where('metode_bayar', 'qris')
            ->whereNull('snap_token')
            ->update(['payment_status' => 'paid']);
    }

    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropColumn([
                'order_id',
                'snap_token',
                'payment_status',
                'midtrans_payment_type',
                'paid_at',
            ]);
        });
    }
};

