<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $table = 'kategori';

    protected $primaryKey = 'id_kategori';

    protected $fillable = [
        'nama_kategori',
        'is_aktif',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];

    /**
     * Satu kategori memiliki banyak menu.
     */
    public function menus()
    {
        return $this->hasMany(
            Menu::class,
            'id_kategori',
            'id_kategori'
        );
    }
}