<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $variant = $this->variants->first();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'vendor' => $this->vendor,
            'product_type' => $this->product_type,
            'tags' => $this->tags ? explode(',', $this->tags) : [],
            'featured' => $this->featured,
            'min_order_qty' => $this->min_order_qty,
            'allow_sell_alone' => (bool) $this->allow_sell_alone,
            'purchase_rule_label' => $this->when(
                $request->routeIs('api.v1.products.show') || ! $this->allow_sell_alone || $this->min_order_qty > 1,
                fn () => $this->purchaseRuleLabel()
            ),
            'companion_products' => $this->whenLoaded('companionProducts', fn () => $this->companionProducts->map(fn ($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
            ])),
            'status' => $this->status,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'price' => $this->minPrice(),
            'compare_at_price' => $variant?->compare_at_price,
            'on_sale' => $this->isOnSale(),
            'sold_out' => $this->isSoldOut(),
            'featured_image' => $this->featuredImageUrl(),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'options' => $this->whenLoaded('options', fn () => $this->options->map(fn ($o) => [
                'name' => $o->name,
                'values' => $o->values->pluck('value'),
            ])),
            'collections' => $this->whenLoaded('collections', fn () => $this->collections->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
            ])),
        ];
    }
}
