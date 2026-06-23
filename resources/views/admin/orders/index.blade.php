@extends('layouts.admin')
@section('title', 'Orders')
@section('header', 'Orders')
@section('content')
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Order</th><th>Customer</th><th>Payment</th><th>Total</th><th>Status</th><th>Date</th></tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td><a href="{{ route('admin.orders.show', $order) }}" class="fw-medium">{{ $order->order_number }}</a></td>
                        <td>{{ $order->customer_name }}<br><small class="text-muted">{{ $order->customer_phone }}</small></td>
                        <td>{{ $order->payment_method }}<br><span class="badge bg-light text-dark">{{ $order->payment_status }}</span></td>
                        <td>Rs.{{ number_format($order->total) }}</td>
                        <td><span class="badge bg-primary">{{ $order->fulfillment_status }}</span></td>
                        <td class="text-muted small">{{ $order->created_at->format('M d, Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())<div class="card-footer bg-white">{{ $orders->links() }}</div>@endif
</div>
@endsection
