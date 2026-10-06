<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_menu'   => $this->id_menu,
            'nama_menu' => $this->nama_menu,
            'kategori'  => $this->kategori,
            'is_aktif'  => $this->is_aktif,
        ];
    }
}