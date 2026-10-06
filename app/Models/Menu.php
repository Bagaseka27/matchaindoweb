<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';
    protected $primaryKey = 'id_menu';

    protected $fillable = ['nama_menu', 'kategori', 'is_aktif'];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];
}