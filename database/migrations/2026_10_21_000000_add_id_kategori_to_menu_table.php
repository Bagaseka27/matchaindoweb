<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('menu', 'id_kategori')) {
            Schema::table('menu', function (Blueprint $table) {
                $table->unsignedBigInteger('id_kategori')->nullable();
                $table->foreign('id_kategori')
                    ->references('id_kategori')
                    ->on('kategori')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasColumn('menu', 'kategori')) {
            $kategoris = DB::table('kategori')->pluck('id_kategori', 'nama_kategori');

            foreach ($kategoris as $namaKategori => $idKategori) {
                DB::table('menu')
                    ->where('kategori', $namaKategori)
                    ->whereNull('id_kategori')
                    ->update(['id_kategori' => $idKategori]);
            }

            Schema::table('menu', function (Blueprint $table) {
                $table->string('kategori', 50)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('menu', 'id_kategori')) {
            Schema::table('menu', function (Blueprint $table) {
                $table->dropForeign(['id_kategori']);
                $table->dropColumn('id_kategori');
            });
        }
    }
};
