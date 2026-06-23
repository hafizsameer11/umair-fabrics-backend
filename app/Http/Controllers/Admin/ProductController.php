<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;
use App\Services\RevalidateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct(private RevalidateService $revalidate) {}

    public function index(Request $request)
    {
        $products = Product::with(['variants', 'images'])
            ->when($request->search, fn ($q) => $q->where('title', 'like', '%'.$request->search.'%'))
            ->latest()
            ->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        return view('admin.products.form', [
            'product' => new Product(['status' => 'draft', 'min_order_qty' => 1, 'allow_sell_alone' => true]),
            'collections' => Collection::orderBy('name')->get(),
            'allProducts' => Product::orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function store(Request $request)
    {
        $product = $this->saveProduct($request, new Product);

        return redirect()->route('admin.products.edit', $product)->with('success', 'Product created.');
    }

    public function edit(Product $product)
    {
        $product->load(['variants', 'images', 'options.values', 'collections', 'companionProducts']);

        return view('admin.products.form', [
            'product' => $product,
            'collections' => Collection::orderBy('name')->get(),
            'allProducts' => Product::where('id', '!=', $product->id)->orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $this->saveProduct($request, $product);

        return redirect()->route('admin.products.edit', $product)->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        foreach ($product->images as $image) {
            if (str_starts_with($image->path, 'products/')) {
                Storage::disk('public')->delete($image->path);
            }
        }

        $slug = $product->slug;
        $product->delete();
        $this->revalidate->revalidateProduct($slug);

        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }

    public function toggleStatus(Product $product)
    {
        $product->update([
            'status' => $product->status === 'active' ? 'draft' : 'active',
        ]);
        $this->revalidate->revalidateProduct($product->slug);

        $label = $product->status === 'active' ? 'activated' : 'deactivated';

        return back()->with('success', "Product {$label} successfully.");
    }

    public function duplicate(Product $product)
    {
        $product->load(['variants', 'images', 'options.values', 'collections', 'companionProducts']);

        $new = DB::transaction(function () use ($product) {
            $copy = $product->replicate();
            $copy->title = $product->title.' (Copy)';
            $copy->slug = Str::slug($copy->title).'-'.Str::random(4);
            $copy->status = 'draft';
            $copy->save();

            foreach ($product->variants as $variant) {
                $v = $variant->replicate();
                $v->product_id = $copy->id;
                $v->save();
            }

            foreach ($product->images as $image) {
                ProductImage::create([
                    'product_id' => $copy->id,
                    'path' => $image->path,
                    'alt' => $image->alt,
                    'sort_order' => $image->sort_order,
                ]);
            }

            foreach ($product->options as $option) {
                $opt = ProductOption::create([
                    'product_id' => $copy->id,
                    'name' => $option->name,
                    'position' => $option->position,
                ]);

                foreach ($option->values as $value) {
                    ProductOptionValue::create([
                        'product_option_id' => $opt->id,
                        'value' => $value->value,
                        'position' => $value->position,
                    ]);
                }
            }

            $copy->collections()->sync($product->collections->pluck('id'));
            $copy->companionProducts()->sync($product->companionProducts->pluck('id'));

            return $copy;
        });

        return redirect()->route('admin.products.edit', $new)->with('success', 'Product duplicated.');
    }

    private function saveProduct(Request $request, Product $product): Product
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description_html' => 'nullable|string',
            'vendor' => 'nullable|string|max:255',
            'product_type' => 'nullable|string|max:255',
            'tags' => 'nullable|string',
            'status' => 'required|in:draft,active',
            'featured' => 'boolean',
            'min_order_qty' => 'required|integer|min:1',
            'max_order_qty' => 'nullable|integer|min:1',
            'allow_sell_alone' => 'boolean',
            'cannot_sell_alone' => 'boolean',
            'companions' => 'nullable|array',
            'companions.*' => 'integer|exists:products,id',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'collections' => 'nullable|array',
            'variants' => 'required|array|min:1',
            'variants.*.id' => 'nullable|integer',
            'variants.*.title' => 'nullable|string',
            'variants.*.sku' => 'nullable|string',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.compare_at_price' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'required|integer|min:0',
            'variants.*.option1' => 'nullable|string',
            'variants.*.option2' => 'nullable|string',
            'variants.*.option3' => 'nullable|string',
            'variants.*.min_order_qty' => 'nullable|integer|min:1',
            'options' => 'nullable|array',
            'options.*.name' => 'required_with:options|string',
            'options.*.values' => 'required_with:options|string',
            'images' => 'nullable|array',
            'images.*' => 'image|max:5120',
            'image_order' => 'nullable|array',
            'existing_images' => 'nullable|array',
            'delete_images' => 'nullable|array',
        ]);

        return DB::transaction(function () use ($request, $product, $data) {
            $slug = $data['slug'] ?: Str::slug($data['title']);

            if (Product::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                $slug .= '-'.Str::random(4);
            }

            $product->fill([
                'title' => $data['title'],
                'slug' => $slug,
                'description_html' => $data['description_html'] ?? '',
                'vendor' => $data['vendor'] ?? null,
                'product_type' => $data['product_type'] ?? null,
                'tags' => $data['tags'] ?? null,
                'status' => $data['status'],
                'featured' => $request->boolean('featured'),
                'min_order_qty' => $data['min_order_qty'],
                'max_order_qty' => $data['max_order_qty'] ?? null,
                'allow_sell_alone' => ! $request->boolean('cannot_sell_alone'),
                'meta_title' => $data['meta_title'] ?? $data['title'],
                'meta_description' => $data['meta_description'] ?? null,
            ]);
            $product->save();

            $product->collections()->sync($data['collections'] ?? []);
            $companions = array_filter($data['companions'] ?? [], fn ($id) => (int) $id !== $product->id);
            $product->companionProducts()->sync($companions);

            $keepIds = [];
            foreach ($data['variants'] as $i => $variantData) {
                $variant = isset($variantData['id'])
                    ? ProductVariant::where('product_id', $product->id)->find($variantData['id'])
                    : null;

                if (! $variant) {
                    $variant = new ProductVariant(['product_id' => $product->id]);
                }

                $variant->fill([
                    'title' => $variantData['title'] ?? null,
                    'sku' => $variantData['sku'] ?? null,
                    'price' => $variantData['price'],
                    'compare_at_price' => filled($variantData['compare_at_price'] ?? null) ? $variantData['compare_at_price'] : null,
                    'stock' => $variantData['stock'],
                    'option1' => $variantData['option1'] ?? null,
                    'option2' => $variantData['option2'] ?? null,
                    'option3' => $variantData['option3'] ?? null,
                    'min_order_qty' => $variantData['min_order_qty'] ?? null,
                    'position' => $i + 1,
                ]);
                $variant->save();
                $keepIds[] = $variant->id;
            }

            ProductVariant::where('product_id', $product->id)->whereNotIn('id', $keepIds)->delete();

            $product->options()->each(fn ($o) => $o->values()->delete());
            $product->options()->delete();

            foreach ($data['options'] ?? [] as $i => $opt) {
                if (empty($opt['name'])) {
                    continue;
                }

                $option = ProductOption::create([
                    'product_id' => $product->id,
                    'name' => $opt['name'],
                    'position' => $i + 1,
                ]);

                foreach (array_filter(array_map('trim', explode(',', $opt['values'] ?? ''))) as $j => $val) {
                    ProductOptionValue::create([
                        'product_option_id' => $option->id,
                        'value' => $val,
                        'position' => $j + 1,
                    ]);
                }
            }

            foreach ($data['delete_images'] ?? [] as $imageId) {
                $image = ProductImage::find($imageId);
                if ($image && $image->product_id === $product->id) {
                    if (str_starts_with($image->path, 'products/')) {
                        Storage::disk('public')->delete($image->path);
                    }
                    $image->delete();
                }
            }

            if ($request->hasFile('images')) {
                $maxOrder = $product->images()->max('sort_order') ?? -1;
                foreach ($request->file('images') as $file) {
                    $path = $file->store('products', 'public');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => $path,
                        'alt' => $product->title,
                        'sort_order' => ++$maxOrder,
                    ]);
                }
            }

            if (! empty($data['image_order'])) {
                foreach ($data['image_order'] as $order => $imageId) {
                    ProductImage::where('product_id', $product->id)->where('id', $imageId)->update(['sort_order' => $order]);
                }
            }

            $this->revalidate->revalidateProduct($product->slug);

            return $product;
        });
    }
}
