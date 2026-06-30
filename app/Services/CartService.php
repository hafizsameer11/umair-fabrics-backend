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

        $quantitiesByVariant = [];
        foreach ($items as $item) {
            $variantId = (int) ($item['variant_id'] ?? 0);
            $quantitiesByVariant[$variantId] = ($quantitiesByVariant[$variantId] ?? 0) + (int) ($item['quantity'] ?? 1);
        }

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
            $maxQty = $variant->maxPurchasableQty();
            $totalForVariant = $quantitiesByVariant[$variant->id] ?? $qty;

            if (! $variant->isAvailable()) {
                $errors[] = [
                    'index' => $index,
                    'variant_id' => $variant->id,
                    'message' => 'This item is out of stock.',
                    'code' => 'out_of_stock',
                    'available' => 0,
                ];

                continue;
            }

            if ($qty < $minQty) {
                $errors[] = [
                    'index' => $index,
                    'variant_id' => $variant->id,
                    'message' => "Minimum order quantity is {$minQty}.",
                    'min_order_qty' => $minQty,
                ];

                continue;
            }

            if ($product->max_order_qty && $totalForVariant > $product->max_order_qty) {
                $errors[] = [
                    'index' => $index,
                    'variant_id' => $variant->id,
                    'message' => "Maximum order quantity is {$product->max_order_qty}.",
                    'code' => 'max_order_qty',
                    'max_order_qty' => $product->max_order_qty,
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

            if ($totalForVariant > $maxQty) {
                $message = $maxQty <= 0
                    ? 'This item is out of stock.'
                    : ($maxQty === 1
                        ? 'Only 1 item left in stock.'
                        : "Only {$maxQty} items available in stock.");

                $errors[] = [
                    'index' => $index,
                    'variant_id' => $variant->id,
                    'message' => $message,
                    'code' => 'insufficient_stock',
                    'available' => $maxQty,
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
