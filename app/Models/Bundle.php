<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Bundle extends Model
{
    protected $fillable = [
        'title', 'slug', 'description', 'image',
        'discount_percent', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'discount_percent' => 'float',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Bundle $bundle) {
            if (empty($bundle->slug)) {
                $bundle->slug = Str::slug($bundle->title);
            }
        });
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'bundle_product')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    public function imageUrl(): ?string
    {
        return $this->image ? asset('storage/'.$this->image) : null;
    }

    public function subtotal(): float
    {
        return $this->products->sum(fn (Product $p) => $p->minPrice());
    }

    public function price(): float
    {
        $subtotal = $this->subtotal();
        $discount = max(0, min(100, (float) $this->discount_percent));

        return round($subtotal * (1 - $discount / 100), 2);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
