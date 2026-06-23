<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'ordersToday' => Order::whereDate('created_at', today())->count(),
            'totalOrders' => Order::count(),
            'totalProducts' => Product::count(),
            'lowStock' => ProductVariant::where('stock', '<', 5)->where('stock', '>', 0)->count(),
            'recentOrders' => Order::latest()->limit(5)->get(),
        ]);
    }
}
