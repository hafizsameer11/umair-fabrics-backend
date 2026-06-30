<?php

namespace App\Models;

use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeroSlide extends Model
{
    protected $fillable = [
        'title', 'subtitle', 'button_text', 'button_url', 'collection_id',
        'image', 'countdown_ends_at', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'countdown_ends_at' => 'datetime',
    ];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function imageUrl(): ?string
    {
        return MediaUrl::fromStoragePath($this->image);
    }

    public function linkUrl(): ?string
    {
        if ($this->collection) {
            return '/collections/'.$this->collection->slug.'/';
        }

        return $this->button_url ?: null;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
