<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'category'     => $this->category,
            'description'  => $this->description,
            'price'        => $this->price,
            'image_url'    => $this->image_url,
            'is_available' => $this->is_available,
        ];
    }
}
