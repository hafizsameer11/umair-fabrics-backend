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
        $shipping = ShippingSetting::first() ?? new ShippingSetting(['flat_rate' => 300, 'is_active' => true]);

        return view('admin.shipping.edit', compact('shipping'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'flat_rate' => 'required|numeric|min:0',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        ShippingSetting::updateOrCreate(['id' => 1], [
            ...$data,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->revalidate->revalidate(['/checkout']);

        return back()->with('success', 'Shipping settings saved.');
    }
}
