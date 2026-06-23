<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Services\RevalidateService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private RevalidateService $revalidate) {}

    public function index()
    {
        $methods = PaymentMethod::orderBy('sort_order')->get();

        return view('admin.payments.index', compact('methods'));
    }

    public function update(Request $request, PaymentMethod $payment)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'instructions' => 'nullable|string',
            'is_enabled' => 'boolean',
        ]);

        $payment->update([
            ...$data,
            'is_enabled' => $request->boolean('is_enabled'),
        ]);

        $this->revalidate->revalidate(['/checkout']);

        return back()->with('success', 'Payment method updated.');
    }
}
