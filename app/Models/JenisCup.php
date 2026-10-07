<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisCup extends Model
{
    protected $table = 'jenis_cup';

    protected $primaryKey = 'id_cup';

    protected $fillable = [
        'nama_cup',
        'volume_ml',
    ];

    /**
     * Satu jenis cup dapat digunakan
     * oleh banyak harga menu.
     */
    public function hargaMenus()
    {
        return $this->hasMany(
            HargaMenu::class,
            'id_cup',
            'id_cup'
        );
    }
}