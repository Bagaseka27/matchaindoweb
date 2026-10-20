<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_cup', function (Blueprint $table) {
            $table->unsignedBigInteger('id_cabang');
            $table->unsignedBigInteger('id_cup');
            $table->unsignedInteger('jumlah_stok')->default(0);
            $table->unsignedInteger('stok_minimum')->default(0);
            $table->dateTime('diperbarui_pada');
            $table->timestamps();

            $table->primary(['id_cabang', 'id_cup']);
            $table->foreign('id_cabang')->references('id_cabang')->on('cabang')->onDelete('cascade');
            $table->foreign('id_cup')->references('id_cup')->on('jenis_cup')->onDelete('cascade');
        });

        Schema::create('mutasi_cup', function (Blueprint $table) {
            $table->id('id_mutasi');
            $table->unsignedBigInteger('id_cabang');
            $table->unsignedBigInteger('id_cup');
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_transaksi')->nullable();
            $table->enum('jenis', ['masuk', 'koreksi', 'pemakaian']);
            $table->unsignedInteger('jumlah');
            $table->dateTime('waktu');
            $table->timestamps();

            $table->foreign('id_cabang')->references('id_cabang')->on('cabang')->onDelete('cascade');
            $table->foreign('id_cup')->references('id_cup')->on('jenis_cup')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('pengguna')->onDelete('cascade');
            $table->foreign('id_transaksi')->references('id_transaksi')->on('transaksi')->onDelete('set null');
            $table->index(['id_cabang', 'id_cup', 'waktu']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutasi_cup');
        Schema::dropIfExists('stok_cup');
    }
};
