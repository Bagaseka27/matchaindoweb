<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{

    protected $table = 'shift';


    protected $primaryKey = 'id_shift';



    protected $fillable = [

        'id_cabang',

        'nama_shift',

        'jam_mulai',

        'jam_selesai'

    ];

}