<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shift extends Model
{
    protected $table = 'shift';

    protected $primaryKey = 'id_shift';

    protected $fillable = [
        'id_cabang',
        'nama_shift',
        'jam_mulai',
        'jam_selesai',
    ];

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(
            Cabang::class,
            'id_cabang',
            'id_cabang'
        );
    }
}