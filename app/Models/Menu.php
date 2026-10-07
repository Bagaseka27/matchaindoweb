<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';

    protected $primaryKey = 'id_menu';

    protected $fillable = [
        'nama_menu',
        'id_kategori',
        'is_aktif',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];

    /**
     * Menu memiliki satu kategori.
     */
    public function kategori()
    {
        return $this->belongsTo(
            Kategori::class,
            'id_kategori',
            'id_kategori'
        );
    }

    /**
     * Menu memiliki banyak harga
     * berdasarkan jenis/ukuran cup.
     */
    public function hargaMenus()
    {
        return $this->hasMany(
            HargaMenu::class,
            'id_menu',
            'id_menu'
        );
    }
}