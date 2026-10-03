<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Menu extends Model
{
    protected $fillable = [
        'name', 'category', 'description', 'price', 'image', 'is_available',
    ];

    protected $casts = [
        'price' => 'integer',
        'is_available' => 'boolean',
    ];

    // URL gambar mengikuti host request (emulator: 10.0.2.2:8000, browser: localhost:8000)
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? url(Storage::url($this->image)) : null;
    }
}
