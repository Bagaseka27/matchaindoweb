<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StokCup extends Model
{
    protected $table = 'stok_cup';

    protected $primaryKey = null;

    public $incrementing = false;

    protected $fillable = [
        'id_cabang',
        'id_cup',
        'jumlah_stok',
        'stok_minimum',
        'diperbarui_pada',
    ];

    protected $casts = [
        'jumlah_stok' => 'integer',
        'stok_minimum' => 'integer',
        'diperbarui_pada' => 'datetime',
    ];

    public function jenisCup(): BelongsTo
    {
        return $this->belongsTo(JenisCup::class, 'id_cup', 'id_cup');
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'id_cabang', 'id_cabang');
    }
}
