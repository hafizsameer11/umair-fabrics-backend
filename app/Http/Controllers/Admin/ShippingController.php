<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingSetting;
use App\Services\RevalidateService;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function __construct(private RevalidateService $revalidate) {}

    public function edit()
    {
        $shipping = ShippingSetting::first() ?? new ShippingSetting([
            'name' => 'Pakistan Flat Rate',
            'flat_rate' => 300,
            'base_fee' => 230,
            'weight_min_grams' => 100,
            'weight_max_grams' => 3000,
            'extra_step_grams' => 1000,
            'extra_step_fee' => 50,
            'is_active' => true,
        ]);

        return view('admin.shipping.edit', compact('shipping'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'weight_min_grams' => 'required|integer|min:0',
            'weight_max_grams' => 'required|integer|min:1|gte:weight_min_grams',
            'base_fee' => 'required|numeric|min:0',
            'extra_step_grams' => 'required|integer|min:1',
            'extra_step_fee' => 'required|numeric|min:0',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        ShippingSetting::updateOrCreate(['id' => 1], [
            ...$data,
            'flat_rate' => $data['base_fee'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        \App\Services\CacheService::flushStorefront();
        $this->revalidate->revalidate(['/', '/checkout']);

        return back()->with('success', 'Shipping settings saved.');
    }
}
