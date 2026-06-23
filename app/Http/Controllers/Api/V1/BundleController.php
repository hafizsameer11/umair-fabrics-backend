<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BundleResource;
use App\Models\Bundle;
use App\Services\CacheService;

class BundleController extends Controller
{
    public function index()
    {
        $bundles = CacheService::remember('api.bundles', 300, function () {
            return Bundle::active()
                ->withCount('products')
                ->with(['products' => fn ($q) => $q->active()])
                ->orderBy('sort_order')
                ->get();
        });

        return BundleResource::collection($bundles);
    }

    public function show(string $slug)
    {
        $bundle = CacheService::remember("api.bundle.{$slug}", 300, function () use ($slug) {
            return Bundle::active()
                ->where('slug', $slug)
                ->with(['products' => fn ($q) => $q->active()->with(['variants', 'images'])])
                ->firstOrFail();
        });

        return new BundleResource($bundle);
    }
}
