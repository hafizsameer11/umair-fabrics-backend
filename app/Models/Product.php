<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'title', 'slug', 'description_html', 'vendor', 'product_type', 'tags',
        'status', 'featured', 'weight_grams', 'min_order_qty', 'max_order_qty', 'show_stock_to_customers', 'allow_sell_alone',
        'meta_title', 'meta_description', 'og_image',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'weight_grams' => 'integer',
        'min_order_qty' => 'integer',
        'max_order_qty' => 'integer',
        'allow_sell_alone' => 'boolean',
        'show_stock_to_customers' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->title);
            }
        });

        static::saved(fn () => \App\Services\CacheService::flushStorefront());
        static::deleted(fn () => \App\Services\CacheService::flushStorefront());
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('position');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function options(): HasMany
    {
        return $this->hasMany(ProductOption::class)->orderBy('position');
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class)->withPivot('sort_order');
    }

    /** Products that must be in cart (or buy min qty) when allow_sell_alone is false */
    public function companionProducts(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_companion',
            'product_id',
            'companion_product_id'
        );
    }

    public function purchaseRuleLabel(): ?string
    {
        if ($this->allow_sell_alone) {
            return $this->min_order_qty > 1
                ? "Minimum {$this->min_order_qty} pieces per order"
                : null;
        }

        $min = max(2, $this->min_order_qty);
        $companions = $this->companionProducts->pluck('title')->take(2)->join(', ');

        if ($companions) {
            return "Buy {$min}+ pieces OR add with: {$companions}";
        }

        return "Buy {$min}+ pieces — cannot order single piece alone";
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function isOnSale(): bool
    {
        return $this->variants->contains(fn ($v) => $v->compare_at_price && $v->compare_at_price > $v->price);
    }

    public function isSoldOut(): bool
    {
        if ($this->variants->isEmpty()) {
            return true;
        }

        return $this->variants->every(fn ($v) => ! $v->isAvailable());
    }

    public function minPrice(): float
    {
        return (float) $this->variants->min('price');
    }

    public function featuredImageUrl(): ?string
    {
        $image = $this->images->first();

        return $image ? $image->url() : null;
    }
}
