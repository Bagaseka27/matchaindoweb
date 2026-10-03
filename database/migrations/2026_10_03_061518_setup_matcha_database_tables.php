<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Tabel Cabang
        Schema::create('cabang', function (Blueprint $table) {
            $table->id('id_cabang');
            $table->string('nama_cabang', 100);
            $table->text('alamat');
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        // 2. Tabel Pengguna (Sesuai ERD, menggantikan users)
        Schema::create('pengguna', function (Blueprint $table) {
            $table->id('id_user');
            $table->unsignedBigInteger('id_cabang')->nullable();
            $table->string('nama', 100);
            $table->string('username', 50)->unique();
            $table->string('password', 255);
            $table->enum('role', ['pemilik', 'barista']);
            $table->decimal('tarif_harian', 12, 2)->default(0);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();

            $table->foreign('id_cabang')->references('id_cabang')->on('cabang')->onDelete('set null');
        });

        // 3. Tabel Shift
        Schema::create('shift', function (Blueprint $table) {
            $table->id('id_shift');
            $table->unsignedBigInteger('id_cabang');
            $table->string('nama_shift', 50);
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->timestamps();

            $table->foreign('id_cabang')->references('id_cabang')->on('cabang')->onDelete('cascade');
        });

        // 4. Tabel Jadwal Shift
        Schema::create('jadwal_shift', function (Blueprint $table) {
            $table->id('id_jadwal');
            $table->unsignedBigInteger('id_shift');
            $table->unsignedBigInteger('id_user');
            $table->date('tanggal');
            $table->timestamps();

            $table->foreign('id_shift')->references('id_shift')->on('shift')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('pengguna')->onDelete('cascade');
        });

        // 5. Tabel Penggajian
        Schema::create('penggajian', function (Blueprint $table) {
            $table->id('id_penggajian');
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_jadwal');
            $table->date('tanggal');
            $table->decimal('nominal_gaji', 12, 2);
            $table->enum('status', ['belum dibayar', 'sudah dibayar'])->default('belum dibayar');
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('pengguna')->onDelete('cascade');
            $table->foreign('id_jadwal')->references('id_jadwal')->on('jadwal_shift')->onDelete('cascade');
        });

        // 6. Tabel Kebijakan Cabang
        Schema::create('kebijakan_cabang', function (Blueprint $table) {
            $table->id('id_kebijakan');
            $table->unsignedBigInteger('id_cabang');
            $table->string('nama_kebijakan', 100);
            $table->string('nilai', 255);
            $table->timestamps();

            $table->foreign('id_cabang')->references('id_cabang')->on('cabang')->onDelete('cascade');
        });

        // 7. Tabel Bahan Baku
        Schema::create('bahan_baku', function (Blueprint $table) {
            $table->id('id_bahan');
            $table->string('nama_bahan', 100);
            $table->string('satuan', 20);
            $table->timestamps();
        });

        // 8. Tabel Stok Bahan
        Schema::create('stok_bahan', function (Blueprint $table) {
            $table->unsignedBigInteger('id_cabang');
            $table->unsignedBigInteger('id_bahan');
            $table->decimal('jumlah_stok', 12, 2);
            $table->decimal('stok_minimum', 12, 2);
            $table->dateTime('diperbarui_pada');
            $table->timestamps();

            $table->primary(['id_cabang', 'id_bahan']);
            $table->foreign('id_cabang')->references('id_cabang')->on('cabang')->onDelete('cascade');
            $table->foreign('id_bahan')->references('id_bahan')->on('bahan_baku')->onDelete('cascade');
        });

        // 9. Tabel Menu
        Schema::create('menu', function (Blueprint $table) {
            $table->id('id_menu');
            $table->string('nama_menu', 100);
            $table->string('kategori', 50);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        // 10. Tabel Jenis Cup
        Schema::create('jenis_cup', function (Blueprint $table) {
            $table->id('id_cup');
            $table->string('nama_cup', 50);
            $table->integer('volume_ml');
            $table->timestamps();
        });

        // 11. Tabel Harga Menu
        Schema::create('harga_menu', function (Blueprint $table) {
            $table->unsignedBigInteger('id_menu');
            $table->unsignedBigInteger('id_cup');
            $table->decimal('harga', 12, 2);
            $table->timestamps();

            $table->primary(['id_menu', 'id_cup']);
            $table->foreign('id_menu')->references('id_menu')->on('menu')->onDelete('cascade');
            $table->foreign('id_cup')->references('id_cup')->on('jenis_cup')->onDelete('cascade');
        });

        // 12. Tabel Transaksi
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id('id_transaksi');
            $table->unsignedBigInteger('id_cabang');
            $table->unsignedBigInteger('id_user');
            $table->integer('urutan_pelanggan');
            $table->dateTime('waktu_transaksi');
            $table->decimal('total_bayar', 12, 2);
            $table->enum('metode_bayar', ['tunai', 'qris']);
            $table->decimal('uang_diterima', 12, 2)->nullable();
            $table->decimal('kembalian', 12, 2)->nullable();
            $table->timestamps();

            $table->foreign('id_cabang')->references('id_cabang')->on('cabang')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('pengguna')->onDelete('cascade');
        });

        // 13. Tabel Detail Transaksi
        Schema::create('detail_transaksi', function (Blueprint $table) {
            $table->id('id_detail');
            $table->unsignedBigInteger('id_transaksi');
            $table->unsignedBigInteger('id_menu');
            $table->unsignedBigInteger('id_cup');
            $table->integer('jumlah');
            $table->decimal('harga_satuan', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();

            $table->foreign('id_transaksi')->references('id_transaksi')->on('transaksi')->onDelete('cascade');
            $table->foreign('id_menu')->references('id_menu')->on('menu')->onDelete('cascade');
            $table->foreign('id_cup')->references('id_cup')->on('jenis_cup')->onDelete('cascade');
        });

        // 14. Tabel Riwayat Stok
        Schema::create('riwayat_stok', function (Blueprint $table) {
            $table->id('id_riwayat');
            $table->unsignedBigInteger('id_cabang');
            $table->unsignedBigInteger('id_bahan');
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_transaksi')->nullable();
            $table->enum('jenis', ['masuk', 'koreksi', 'pemakaian']);
            $table->decimal('jumlah', 12, 2);
            $table->dateTime('waktu');
            $table->timestamps();

            $table->foreign('id_cabang')->references('id_cabang')->on('cabang')->onDelete('cascade');
            $table->foreign('id_bahan')->references('id_bahan')->on('bahan_baku')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('pengguna')->onDelete('cascade');
            $table->foreign('id_transaksi')->references('id_transaksi')->on('transaksi')->onDelete('set null');
        });

        // 15. Tabel Rekonsiliasi QRIS
        Schema::create('rekonsiliasi_qris', function (Blueprint $table) {
            $table->id('id_rekon');
            $table->unsignedBigInteger('id_cabang');
            $table->unsignedBigInteger('id_user');
            $table->date('periode_awal');
            $table->date('periode_akhir');
            $table->decimal('total_sistem', 14, 2);
            $table->decimal('total_aktual', 14, 2);
            $table->decimal('selisih', 14, 2);
            $table->dateTime('dibuat_pada');
            $table->timestamps();

            $table->foreign('id_cabang')->references('id_cabang')->on('cabang')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('pengguna')->onDelete('cascade');
        });

        // =========================================================
        // TABEL BAWAAN LARAVEL (Mencegah Error 500 Session/Auth)
        // =========================================================

        // 16. Tabel Sessions
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // 17. Tabel Password Reset Tokens
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down()
    {
        // Drop tabel bawaan Laravel
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');

        // Drop tabel ERD (urutan dari bawah ke atas agar foreign key tidak error)
        Schema::dropIfExists('rekonsiliasi_qris');
        Schema::dropIfExists('riwayat_stok');
        Schema::dropIfExists('detail_transaksi');
        Schema::dropIfExists('transaksi');
        Schema::dropIfExists('harga_menu');
        Schema::dropIfExists('jenis_cup');
        Schema::dropIfExists('menu');
        Schema::dropIfExists('stok_bahan');
        Schema::dropIfExists('bahan_baku');
        Schema::dropIfExists('kebijakan_cabang');
        Schema::dropIfExists('penggajian');
        Schema::dropIfExists('jadwal_shift');
        Schema::dropIfExists('shift');
        Schema::dropIfExists('pengguna');
        Schema::dropIfExists('cabang');
    }
};