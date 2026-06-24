<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ShippingSetting;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(private CartService $cartService) {}

    /**
     * @param  array<int, array{variant_id: int, quantity: int}>  $items
     */
    public function create(array $customer, array $items, string $paymentMethod): Order
    {
        $validation = $this->cartService->validate($items);

        if (! $validation['valid']) {
            throw new \InvalidArgumentException(json_encode($validation['errors']));
        }

        $shipping = ShippingSetting::where('is_active', true)->first();
        $subtotal = $validation['items']->sum('line_total');
        $shippingCost = 0;

        if ($shipping) {
            $threshold = $shipping->free_shipping_threshold;
            $shippingCost = ($threshold && $subtotal >= $threshold) ? 0 : (float) $shipping->flat_rate;
        }

        return DB::transaction(function () use ($customer, $validation, $paymentMethod, $subtotal, $shippingCost) {
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'customer_name' => $customer['name'],
                'customer_phone' => $customer['phone'],
                'customer_phone_alt' => $customer['phone_alt'] ?? null,
                'customer_email' => $customer['email'] ?? null,
                'shipping_address' => $customer['address'],
                'city' => $customer['city'],
                'billing_address' => $customer['billing_address'] ?? $customer['address'],
                'billing_city' => $customer['billing_city'] ?? $customer['city'],
                'notes' => $customer['notes'] ?? null,
                'payment_method' => $paymentMethod,
                'payment_status' => 'pending',
                'fulfillment_status' => 'unfulfilled',
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total' => $subtotal + $shippingCost,
            ]);

            foreach ($validation['items'] as $item) {
                $variant = $item['variant'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'product_title' => $variant->product->title,
                    'variant_title' => $variant->title,
                    'sku' => $variant->sku,
                    'quantity' => $item['quantity'],
                    'unit_price' => $variant->price,
                    'line_total' => $item['line_total'],
                ]);

                $variant->decrement('stock', $item['quantity']);
            }

            return $order->load('items');
        });
    }
}
