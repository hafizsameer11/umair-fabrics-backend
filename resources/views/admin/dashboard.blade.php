@extends('layouts.admin')
@section('title', 'Dashboard')
@section('header', 'Dashboard')
@section('content')
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold">Orders Today</div>
                <div class="stat-value text-primary">{{ $ordersToday }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold">Total Orders</div>
                <div class="stat-value">{{ $totalOrders }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold">Products</div>
                <div class="stat-value text-success">{{ $totalProducts }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold">Low Stock</div>
                <div class="stat-value text-warning">{{ $lowStock }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h2 class="h6 mb-0 fw-semibold"><i class="bi bi-clock-history me-2"></i>Recent Orders</h2>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentOrders as $order)
                    <tr>
                        <td><a href="{{ route('admin.orders.show', $order) }}" class="fw-medium">{{ $order->order_number }}</a></td>
                        <td>{{ $order->customer_name }}<br><small class="text-muted">{{ $order->customer_phone }}</small></td>
                        <td>Rs.{{ number_format($order->total) }}</td>
                        <td><span class="badge bg-secondary">{{ $order->fulfillment_status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-5">No orders yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
