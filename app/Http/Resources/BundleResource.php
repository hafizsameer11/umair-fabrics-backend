<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BundleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $subtotal = $this->subtotal();
        $price = $this->price();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'image' => $this->imageUrl(),
            'discount_percent' => (float) $this->discount_percent,
            'price' => $price,
            'compare_at_price' => $subtotal > $price ? $subtotal : null,
            'on_sale' => $this->discount_percent > 0,
            'product_count' => $this->whenCounted('products', fn () => $this->products_count),
            'products' => $this->whenLoaded('products', fn () => $this->products->map(fn ($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
                'featured_image' => $p->featuredImageUrl(),
                'price' => $p->minPrice(),
            ])),
        ];
    }
}
