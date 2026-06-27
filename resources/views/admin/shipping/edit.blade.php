@extends('layouts.admin')
@section('title', 'Shipping')
@section('header', 'Shipping Settings')
@section('content')
@php
    $baseFee = old('base_fee', $shipping->base_fee ?? $shipping->flat_rate ?? 230);
@endphp
<form method="POST" action="{{ route('admin.shipping.update') }}">
    @csrf @method('PUT')
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">Weight-based shipping (Pakistan)</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Shipping method name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $shipping->name) }}" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Minimum weight (grams)</label>
                            <input type="number" name="weight_min_grams" class="form-control" value="{{ old('weight_min_grams', $shipping->weight_min_grams ?? 100) }}" min="0" required>
                            <div class="form-text">Base rate applies from this weight upward.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Base weight range up to (grams)</label>
                            <input type="number" name="weight_max_grams" class="form-control" value="{{ old('weight_max_grams', $shipping->weight_max_grams ?? 3000) }}" min="1" required>
                            <div class="form-text">Example: 100–3000g uses the base fee below.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Base shipping fee (PKR)</label>
                        <input type="number" name="base_fee" class="form-control" value="{{ $baseFee }}" step="0.01" min="0" required>
                        <div class="form-text">Charged for orders within the base weight range.</div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Extra weight step (grams)</label>
                            <input type="number" name="extra_step_grams" class="form-control" value="{{ old('extra_step_grams', $shipping->extra_step_grams ?? 1000) }}" min="1" required>
                            <div class="form-text">Each block above the max weight (e.g. +1000g).</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Extra fee per step (PKR)</label>
                            <input type="number" name="extra_step_fee" class="form-control" value="{{ old('extra_step_fee', $shipping->extra_step_fee ?? 50) }}" step="0.01" min="0" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Free shipping above order total (PKR)</label>
                        <input type="number" name="free_shipping_threshold" class="form-control" value="{{ old('free_shipping_threshold', $shipping->free_shipping_threshold) }}" step="0.01" placeholder="Leave empty to disable">
                    </div>

                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $shipping->is_active ?? true))>
                        <label class="form-check-label">Active</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <h3 class="h6 fw-semibold">How it works</h3>
                    <p class="small text-muted mb-2">Total cart weight = sum of each product weight × quantity.</p>
                    <ul class="small text-muted mb-3">
                        <li>100g – 3000g → <strong>Rs 230</strong> (example)</li>
                        <li>3001g – 4000g → base + 1 step</li>
                        <li>4001g – 5000g → base + 2 steps</li>
                    </ul>
                    <p class="small mb-0">Set each product weight in <strong>Products → Edit → Weight (grams)</strong>.</p>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 mt-3">Save shipping settings</button>
        </div>
    </div>
</form>
@endsection
