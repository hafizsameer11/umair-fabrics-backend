<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with('items')
            ->when($request->status, fn ($q) => $q->where('fulfillment_status', $request->status))
            ->latest()
            ->paginate(20);

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('items');

        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'payment_status' => 'required|in:pending,confirmed,failed',
            'fulfillment_status' => 'required|in:unfulfilled,confirmed,shipped,delivered,cancelled',
        ]);

        $order->update($data);

        return back()->with('success', 'Order updated.');
    }
}
