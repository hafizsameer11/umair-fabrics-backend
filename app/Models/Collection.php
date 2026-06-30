<?php

namespace App\Models;

use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Collection extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'image', 'sort_order',
        'show_on_homepage', 'homepage_title', 'homepage_product_limit',
    ];

    protected $casts = [
        'show_on_homepage' => 'boolean',
        'sort_order' => 'integer',
        'homepage_product_limit' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Collection $collection) {
            if (empty($collection->slug)) {
                $collection->slug = Str::slug($collection->name);
            }
        });

        static::saved(fn () => \App\Services\CacheService::flushStorefront());
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withPivot('sort_order')->orderByPivot('sort_order');
    }

    public function homepageSections(): HasMany
    {
        return $this->hasMany(HomepageSection::class);
    }

    public function imageUrl(): ?string
    {
        return MediaUrl::fromStoragePath($this->image);
    }
}
