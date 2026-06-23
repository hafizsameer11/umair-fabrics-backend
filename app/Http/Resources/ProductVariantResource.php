<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'sku' => $this->sku,
            'price' => (float) $this->price,
            'compare_at_price' => $this->compare_at_price ? (float) $this->compare_at_price : null,
            'stock' => $this->stock,
            'available' => $this->isAvailable(),
            'option1' => $this->option1,
            'option2' => $this->option2,
            'option3' => $this->option3,
            'min_order_qty' => $this->effectiveMinQty(),
            'sale_percent' => $this->salePercent(),
        ];
    }
}
