@extends('layouts.admin')
@section('title', 'Shipping')
@section('header', 'Shipping Settings')
@section('content')
<form method="POST" action="{{ route('admin.shipping.update') }}">
    @csrf @method('PUT')
    <div class="card border-0 shadow-sm" style="max-width:560px;">
        <div class="card-body">
            <div class="mb-3"><label class="form-label fw-medium">Name</label><input type="text" name="name" class="form-control" value="{{ $shipping->name }}"></div>
            <div class="mb-3"><label class="form-label">Flat rate (PKR)</label><input type="number" name="flat_rate" class="form-control" value="{{ $shipping->flat_rate }}" step="0.01" min="0"></div>
            <div class="mb-3"><label class="form-label">Free shipping above (PKR)</label><input type="number" name="free_shipping_threshold" class="form-control" value="{{ $shipping->free_shipping_threshold }}" step="0.01" placeholder="Leave empty to disable"></div>
            <div class="form-check form-switch mb-4"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($shipping->is_active)><label class="form-check-label">Active</label></div>
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </div>
</form>
@endsection
