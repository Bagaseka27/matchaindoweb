<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StokBahan extends Model
{
    protected $table = 'stok_bahan';

    protected $primaryKey = null;
    public $incrementing = false;

    /** Stok dianggap "Hampir Habis" jika <= batas minimal x faktor ini. */
    public const FAKTOR_HAMPIR_HABIS = 1.5;

    protected $fillable = [
        'id_cabang',
        'id_bahan',
        'jumlah_stok',
        'stok_minimum',
        'diperbarui_pada',
    ];

    protected $casts = [
        'jumlah_stok'     => 'decimal:2',
        'stok_minimum'    => 'decimal:2',
        'diperbarui_pada' => 'datetime',
    ];

    public function bahan(): BelongsTo
    {
        return $this->belongsTo(BahanBaku::class, 'id_bahan', 'id_bahan');
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'id_cabang', 'id_cabang');
    }

    /** kritis | hampir_habis | aman */
    public function getStatusAttribute(): string
    {
        $sisa = (float) $this->jumlah_stok;
        $min  = (float) $this->stok_minimum;

        if ($sisa <= $min) {
            return 'kritis';
        }

        if ($sisa <= $min * self::FAKTOR_HAMPIR_HABIS) {
            return 'hampir_habis';
        }

        return 'aman';
    }

    /** 45.00 -> "45", 1000.50 -> "1.000,5" */
    public static function formatAngka(int|float|string|null $nilai): string
    {
        return number_format((float) $nilai, 0, ',', '.');
    }

    public function getJumlahStokFmtAttribute(): string
    {
        return self::formatAngka($this->jumlah_stok);
    }

    public function getStokMinimumFmtAttribute(): string
    {
        return self::formatAngka($this->stok_minimum);
    }
}