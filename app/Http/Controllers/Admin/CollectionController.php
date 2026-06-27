<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Product;
use App\Services\RevalidateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CollectionController extends Controller
{
    public function __construct(private RevalidateService $revalidate) {}

    public function index()
    {
        $collections = Collection::withCount('products')->orderBy('sort_order')->get();

        return view('admin.collections.index', compact('collections'));
    }

    public function create()
    {
        return view('admin.collections.form', [
            'collection' => new Collection,
            'sortedProducts' => Product::active()->orderBy('title')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $collection = $this->save($request, new Collection);

        return redirect()->route('admin.collections.edit', $collection)->with('success', 'Collection created.');
    }

    public function edit(Collection $collection)
    {
        $collection->load('products');

        return view('admin.collections.form', [
            'collection' => $collection,
            'sortedProducts' => Product::active()->orderBy('title')->get(),
        ]);
    }

    public function update(Request $request, Collection $collection)
    {
        $this->save($request, $collection);

        return redirect()->route('admin.collections.edit', $collection)->with('success', 'Collection updated.');
    }

    public function destroy(Collection $collection)
    {
        $collection->delete();
        $this->revalidate->revalidate(['/', '/collections']);

        return redirect()->route('admin.collections.index')->with('success', 'Collection deleted.');
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:collections,id',
        ]);

        foreach ($data['order'] as $index => $id) {
            Collection::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        $this->revalidate->revalidate(['/']);

        return back()->with('success', 'Collection order updated.');
    }

    private function save(Request $request, Collection $collection): Collection
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'integer',
            'show_on_homepage' => 'boolean',
            'homepage_title' => 'nullable|string',
            'homepage_product_limit' => 'nullable|integer|min:1|max:48',
            'image' => 'nullable|image|max:2048',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'integer|exists:products,id',
        ]);

        $slug = $data['slug'] ?: Str::slug($data['name']);
        $collection->fill([
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'show_on_homepage' => $request->boolean('show_on_homepage'),
            'homepage_title' => $data['homepage_title'] ?? $data['name'],
            'homepage_product_limit' => $data['homepage_product_limit'] ?? 8,
        ]);

        if ($request->hasFile('image')) {
            if ($collection->image) {
                Storage::disk('public')->delete($collection->image);
            }
            $collection->image = $request->file('image')->store('collections', 'public');
        }

        $collection->save();

        $sync = [];
        foreach ($data['product_ids'] ?? [] as $index => $productId) {
            $sync[(int) $productId] = ['sort_order' => $index + 1];
        }
        $collection->products()->sync($sync);

        $this->revalidate->revalidate(['/', '/collections/'.$collection->slug]);

        return $collection;
    }
}
