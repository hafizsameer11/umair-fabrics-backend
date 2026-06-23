<?php

namespace App\Services;

use App\Models\ProductVariant;
use Illuminate\Support\Collection;

class CartService
{
    /**
     * @param  array<int, array{variant_id: int, quantity: int}>  $items
     * @return array{valid: bool, items: Collection, errors: array}
     */
    public function validate(array $items): array
    {
        $errors = [];
        $validated = collect();

        $cartProductIds = collect($items)->map(function ($item) {
            $variant = ProductVariant::find($item['variant_id'] ?? 0);

            return $variant?->product_id;
        })->filter()->unique()->values();

        foreach ($items as $index => $item) {
            $variant = ProductVariant::with(['product.companionProducts'])->find($item['variant_id'] ?? 0);

            if (! $variant || $variant->product->status !== 'active') {
                $errors[] = ['index' => $index, 'message' => 'Product not available.'];

                continue;
            }

            $product = $variant->product;
            $qty = (int) ($item['quantity'] ?? 1);
            $minQty = $variant->effectiveMinQty();

            if ($qty < $minQty) {
                $errors[] = [
                    'index' => $index,
                    'variant_id' => $variant->id,
                    'message' => "Minimum order quantity is {$minQty}.",
                    'min_order_qty' => $minQty,
                ];

                continue;
            }

            if (! $product->allow_sell_alone) {
                $requiredMin = max(2, $product->min_order_qty);
                $hasCompanion = $product->companionProducts
                    ->pluck('id')
                    ->intersect($cartProductIds)
                    ->isNotEmpty();

                if ($qty < $requiredMin && ! $hasCompanion) {
                    $companionNames = $product->companionProducts->pluck('title')->take(3)->join(', ');
                    $message = $companionNames
                        ? "This item cannot be bought alone. Order {$requiredMin}+ pieces OR add one of: {$companionNames}"
                        : "This item cannot be bought alone. Minimum {$requiredMin} pieces required.";

                    $errors[] = [
                        'index' => $index,
                        'variant_id' => $variant->id,
                        'message' => $message,
                        'code' => 'sell_alone_restricted',
                        'min_order_qty' => $requiredMin,
                        'companion_slugs' => $product->companionProducts->pluck('slug'),
                    ];

                    continue;
                }
            }

            if ($variant->stock < $qty) {
                $errors[] = [
                    'index' => $index,
                    'variant_id' => $variant->id,
                    'message' => 'Insufficient stock.',
                    'available' => $variant->stock,
                ];

                continue;
            }

            $validated->push([
                'variant' => $variant,
                'quantity' => $qty,
                'line_total' => $variant->price * $qty,
            ]);
        }

        return [
            'valid' => empty($errors),
            'items' => $validated,
            'errors' => $errors,
        ];
    }
}
