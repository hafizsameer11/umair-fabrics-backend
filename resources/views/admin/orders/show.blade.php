@extends('layouts.admin')
@section('title', $order->order_number)
@section('header', 'Order '.$order->order_number)
@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Order items</div>
            <div class="table-responsive">
                <table class="table mb-0">
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product_title }} @if($item->variant_title)<small class="text-muted">/ {{ $item->variant_title }}</small>@endif</td>
                            <td>{{ $item->quantity }} × Rs.{{ number_format($item->unit_price) }}</td>
                            <td class="text-end">Rs.{{ number_format($item->line_total) }}</td>
                        </tr>
                    @endforeach
                    <tr class="table-light"><td colspan="2">Subtotal</td><td class="text-end">Rs.{{ number_format($order->subtotal) }}</td></tr>
                    <tr><td colspan="2">Shipping</td><td class="text-end">Rs.{{ number_format($order->shipping_cost) }}</td></tr>
                    <tr class="fw-bold"><td colspan="2">Total</td><td class="text-end">Rs.{{ number_format($order->total) }}</td></tr>
                </table>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Customer</div>
            <div class="card-body">
                <p class="mb-1"><strong>{{ $order->customer_name }}</strong></p>
                <p class="mb-1">{{ $order->customer_phone }}</p>
                @if($order->customer_phone_alt)<p class="mb-1 text-muted">Alt: {{ $order->customer_phone_alt }}</p>@endif
                @if($order->customer_email)<p class="mb-1">{{ $order->customer_email }}</p>@endif
                <p class="mb-1"><strong>Shipping:</strong> {{ $order->shipping_address }}, {{ $order->city }}</p>
                <p class="mb-0"><strong>Billing:</strong> {{ $order->billing_address ?? $order->shipping_address }}, {{ $order->billing_city ?? $order->city }}</p>
                @if($order->notes)<p class="mt-2 text-muted small">Notes: {{ $order->notes }}</p>@endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Update status</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.orders.update', $order) }}">
                    @csrf @method('PUT')
                    <label class="form-label">Payment</label>
                    <select name="payment_status" class="form-select mb-3">
                        @foreach(['pending','confirmed','failed'] as $s)<option value="{{ $s }}" @selected($order->payment_status===$s)>{{ ucfirst($s) }}</option>@endforeach
                    </select>
                    <label class="form-label">Fulfillment</label>
                    <select name="fulfillment_status" class="form-select mb-3">
                        @foreach(['unfulfilled','confirmed','shipped','delivered','cancelled'] as $s)<option value="{{ $s }}" @selected($order->fulfillment_status===$s)>{{ ucfirst($s) }}</option>@endforeach
                    </select>
                    <button type="submit" class="btn btn-primary w-100">Update</button>
                </form>
                <p class="small text-muted mt-3 mb-0">Payment: {{ $order->payment_method }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
