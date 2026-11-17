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
            'kategori'  => $this->resource->relationLoaded('kategori')
                ? $this->resource->getRelation('kategori')?->nama_kategori
                : $this->resource->getAttribute('kategori'),
                
            'foto'      => $this->foto
                ? (
                    str_starts_with($this->foto, 'http://') || str_starts_with($this->foto, 'https://')
                        ? $this->foto
                        : rtrim($request->getSchemeAndHttpHost(), '/') . '/storage/' . ltrim($this->foto, '/')
                )
                : null,
            
            'is_aktif'  => $this->is_aktif,
            'harga' => $this->whenLoaded('hargaMenus', fn () => $this->hargaMenus
                ->map(fn ($harga) => [
                    'id_cup' => $harga->id_cup,
                    'nama_cup' => $harga->jenisCup?->nama_cup,
                    'volume_ml' => $harga->jenisCup?->volume_ml,
                    'harga' => (float) $harga->harga,
                ])
                ->values()),
        ];
    }
}