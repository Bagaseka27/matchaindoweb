<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'pengguna';
    protected $primaryKey = 'id_user';

    protected $fillable = [
        'id_cabang',
        'nama',
        'username',
        'password',
        'role',
        'tarif_harian',
        'is_aktif',
    ];

    protected $hidden = [
        'password',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'id_cabang', 'id_cabang');
    }
}