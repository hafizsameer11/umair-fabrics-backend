<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email',
            'shipping_address' => 'required|string',
            'city' => 'required|string|max:100',
            'notes' => 'nullable|string',
            'payment_method' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if (! PaymentMethod::where('code', $data['payment_method'])->where('is_enabled', true)->exists()) {
            return response()->json(['message' => 'Invalid payment method.'], 422);
        }

        try {
            $order = $this->orderService->create(
                [
                    'name' => $data['customer_name'],
                    'phone' => $data['customer_phone'],
                    'email' => $data['customer_email'] ?? null,
                    'address' => $data['shipping_address'],
                    'city' => $data['city'],
                    'notes' => $data['notes'] ?? null,
                ],
                $data['items'],
                $data['payment_method']
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => 'Cart validation failed.', 'errors' => json_decode($e->getMessage(), true)], 422);
        }

        return response()->json([
            'order_number' => $order->order_number,
            'total' => (float) $order->total,
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status,
        ], 201);
    }
}
