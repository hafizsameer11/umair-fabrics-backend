<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\Product;
use App\Services\CacheService;
use App\Services\RevalidateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BundleController extends Controller
{
    public function __construct(private RevalidateService $revalidate) {}

    public function index()
    {
        $bundles = Bundle::withCount('products')->orderBy('sort_order')->get();

        return view('admin.bundles.index', compact('bundles'));
    }

    public function create()
    {
        $products = Product::active()->orderBy('title')->get(['id', 'title']);

        return view('admin.bundles.form', ['bundle' => new Bundle, 'products' => $products]);
    }

    public function store(Request $request)
    {
        $bundle = $this->saveBundle(new Bundle, $request);
        $this->revalidate->revalidate(['/', '/bundles']);

        return redirect()->route('admin.bundles.edit', $bundle)->with('success', 'Bundle created.');
    }

    public function edit(Bundle $bundle)
    {
        $bundle->load('products');
        $products = Product::active()->orderBy('title')->get(['id', 'title']);

        return view('admin.bundles.form', compact('bundle', 'products'));
    }

    public function update(Request $request, Bundle $bundle)
    {
        $this->saveBundle($bundle, $request);
        $this->revalidate->revalidate(['/', '/bundles']);

        return back()->with('success', 'Bundle updated.');
    }

    public function destroy(Bundle $bundle)
    {
        if ($bundle->image) {
            Storage::disk('public')->delete($bundle->image);
        }
        $bundle->delete();
        CacheService::flushStorefront();
        $this->revalidate->revalidate(['/', '/bundles']);

        return redirect()->route('admin.bundles.index')->with('success', 'Bundle deleted.');
    }

    private function saveBundle(Bundle $bundle, Request $request): Bundle
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'discount_percent' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
            'image' => 'nullable|image|max:4096',
        ]);

        $bundle->fill([
            'title' => $data['title'],
            'slug' => $data['slug'] ?: Str::slug($data['title']),
            'description' => $data['description'] ?? null,
            'discount_percent' => $data['discount_percent'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($request->hasFile('image')) {
            if ($bundle->image) {
                Storage::disk('public')->delete($bundle->image);
            }
            $bundle->image = $request->file('image')->store('bundles', 'public');
        }

        if (! $bundle->exists) {
            $bundle->sort_order = Bundle::max('sort_order') + 1;
        }

        $bundle->save();

        $sync = [];
        foreach ($request->input('product_ids', []) as $i => $productId) {
            $sync[$productId] = ['sort_order' => $i + 1];
        }
        $bundle->products()->sync($sync);

        CacheService::flushStorefront();

        return $bundle;
    }
}
