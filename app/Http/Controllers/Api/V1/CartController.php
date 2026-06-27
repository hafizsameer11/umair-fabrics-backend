<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\ShippingService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private ShippingService $shippingService
    ) {}

    public function validate(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $result = $this->cartService->validate($request->items);

        $items = $result['items']->map(fn ($item) => [
            'variant_id' => $item['variant']->id,
            'product_title' => $item['variant']->product->title,
            'variant_title' => $item['variant']->title,
            'quantity' => $item['quantity'],
            'unit_price' => (float) $item['variant']->price,
            'line_total' => (float) $item['line_total'],
            'min_order_qty' => $item['variant']->effectiveMinQty(),
            'featured_image' => $item['variant']->product->featuredImageUrl(),
        ]);

        $subtotal = (float) $items->sum('line_total');
        $payload = [
            'valid' => $result['valid'],
            'errors' => $result['errors'],
            'items' => $items,
            'subtotal' => $subtotal,
        ];

        if ($result['valid']) {
            $shipping = $this->shippingService->calculateForCart($result['items'], $subtotal);
            $payload = array_merge($payload, $shipping, ['shipping_cost' => $shipping['cost']]);
        }

        return response()->json($payload, $result['valid'] ? 200 : 422);
    }

    public function shipping(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $quote = $this->shippingService->quoteFromLineItems($request->items);

        return response()->json($quote);
    }
}
