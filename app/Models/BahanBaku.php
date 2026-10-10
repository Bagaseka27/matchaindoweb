<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BahanBaku extends Model
{
    protected $table = 'bahan_baku';
    protected $primaryKey = 'id_bahan';

    protected $fillable = ['nama_bahan', 'satuan'];

    public function stok(): HasMany
    {
        return $this->hasMany(StokBahan::class, 'id_bahan', 'id_bahan');
    }
}