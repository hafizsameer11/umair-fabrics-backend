<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\CacheService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $cacheKey = 'api.products.'.md5(json_encode($request->query()));

        $products = CacheService::remember($cacheKey, 300, function () use ($request) {
            $query = Product::active()
                ->with(['variants', 'images'])
                ->latest();

            if ($request->filled('collection')) {
                $query->whereHas('collections', fn ($q) => $q->where('slug', $request->collection));
            }

            if ($request->filled('featured')) {
                $query->where('featured', true);
            }

            if ($request->filled('search')) {
                $search = trim((string) $request->search);
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%')
                        ->orWhereHas('variants', fn ($v) => $v->where('sku', 'like', '%'.$search.'%'));
                });
            }

            if ($request->filled('code')) {
                $code = trim((string) $request->code);
                $query->whereHas('variants', fn ($v) => $v->where('sku', 'like', '%'.$code.'%'));
            }

            return $query->paginate($request->integer('per_page', 24));
        });

        return ProductResource::collection($products);
    }

    public function show(string $slug)
    {
        $product = CacheService::remember("api.product.{$slug}", 300, function () use ($slug) {
            return Product::active()
                ->where('slug', $slug)
                ->with(['variants', 'images', 'options.values', 'collections', 'companionProducts'])
                ->firstOrFail();
        });

        return new ProductResource($product);
    }
}
