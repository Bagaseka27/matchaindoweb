<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HargaMenu extends Model
{
    protected $table = 'harga_menu';

    public $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = [
        'id_menu',
        'id_cup',
        'harga',
    ];

    public function menu()
    {
        return $this->belongsTo(
            Menu::class,
            'id_menu',
            'id_menu'
        );
    }

    public function jenisCup()
    {
        return $this->belongsTo(
            JenisCup::class,
            'id_cup',
            'id_cup'
        );
    }
}