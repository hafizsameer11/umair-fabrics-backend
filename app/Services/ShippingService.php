<?php

namespace App\Services;

use App\Models\ShippingSetting;
use Illuminate\Support\Collection;

class ShippingService
{
    /**
     * @param  Collection<int, array{variant: \App\Models\ProductVariant, quantity: int}>  $cartItems
     * @return array{cost: float, weight_grams: int, free_shipping: bool}
     */
    public function calculateForCart(Collection $cartItems, float $subtotal): array
    {
        $settings = ShippingSetting::where('is_active', true)->first();

        if (! $settings) {
            return ['cost' => 0.0, 'weight_grams' => 0, 'free_shipping' => false];
        }

        $weight = $this->totalWeightGrams($cartItems);

        if ($settings->free_shipping_threshold && $subtotal >= (float) $settings->free_shipping_threshold) {
            return ['cost' => 0.0, 'weight_grams' => $weight, 'free_shipping' => true];
        }

        return [
            'cost' => $this->calculateFee($weight, $settings),
            'weight_grams' => $weight,
            'free_shipping' => false,
        ];
    }

    /**
     * @param  Collection<int, array{variant: \App\Models\ProductVariant, quantity: int}>  $cartItems
     */
    public function totalWeightGrams(Collection $cartItems): int
    {
        return (int) $cartItems->sum(function ($item) {
            $grams = (int) ($item['variant']->product->weight_grams ?? 0);

            return $grams * (int) $item['quantity'];
        });
    }

    public function calculateFee(int $weightGrams, ?ShippingSetting $settings = null): float
    {
        $settings ??= ShippingSetting::where('is_active', true)->first();

        if (! $settings) {
            return 0.0;
        }

        $min = max(0, (int) ($settings->weight_min_grams ?? 0));
        $max = max($min, (int) ($settings->weight_max_grams ?? 3000));
        $baseFee = (float) ($settings->base_fee ?? $settings->flat_rate ?? 0);
        $stepGrams = max(1, (int) ($settings->extra_step_grams ?? 1000));
        $stepFee = (float) ($settings->extra_step_fee ?? 0);

        $weight = max($weightGrams, $min);

        if ($weight <= $max) {
            return round($baseFee, 2);
        }

        $extra = $weight - $max;
        $steps = (int) ceil($extra / $stepGrams);

        return round($baseFee + ($steps * $stepFee), 2);
    }

    /**
     * @param  array<int, array{variant_id: int, quantity: int}>  $items
     * @return array{cost: float, weight_grams: int, free_shipping: bool, subtotal: float, shipping_cost: float}
     */
    public function quoteFromLineItems(array $items): array
    {
        $lines = collect();
        $subtotal = 0.0;

        foreach ($items as $item) {
            $variant = \App\Models\ProductVariant::with('product')->find($item['variant_id'] ?? 0);
            if (! $variant) {
                continue;
            }

            $qty = (int) ($item['quantity'] ?? 1);
            $lines->push(['variant' => $variant, 'quantity' => $qty]);
            $subtotal += (float) $variant->price * $qty;
        }

        $shipping = $this->calculateForCart($lines, $subtotal);

        return [
            ...$shipping,
            'subtotal' => round($subtotal, 2),
            'shipping_cost' => $shipping['cost'],
        ];
    }

    public function rulesForApi(?ShippingSetting $settings): ?array
    {
        if (! $settings || ! $settings->is_active) {
            return null;
        }

        return [
            'name' => $settings->name,
            'weight_min_grams' => (int) $settings->weight_min_grams,
            'weight_max_grams' => (int) $settings->weight_max_grams,
            'base_fee' => (float) ($settings->base_fee ?? $settings->flat_rate),
            'extra_step_grams' => (int) $settings->extra_step_grams,
            'extra_step_fee' => (float) $settings->extra_step_fee,
            'free_shipping_threshold' => $settings->free_shipping_threshold ? (float) $settings->free_shipping_threshold : null,
            'flat_rate' => (float) ($settings->base_fee ?? $settings->flat_rate),
        ];
    }
}
