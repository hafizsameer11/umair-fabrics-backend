<?php

namespace App\Models;

use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Model;

class HeroSlide extends Model
{
    protected $fillable = [
        'title', 'subtitle', 'button_text', 'button_url',
        'image', 'countdown_ends_at', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'countdown_ends_at' => 'datetime',
    ];

    public function imageUrl(): ?string
    {
        return MediaUrl::fromStoragePath($this->image);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
