<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\ProductResource;
use App\Models\Collection;
use App\Services\CacheService;

class CollectionController extends Controller
{
    public function index()
    {
        $collections = CacheService::remember('api.collections', 300, function () {
            return Collection::orderBy('sort_order')->get();
        });

        return CollectionResource::collection($collections);
    }

    public function show(string $slug)
    {
        $collection = CacheService::remember("api.collection.{$slug}", 300, function () use ($slug) {
            return Collection::where('slug', $slug)
                ->with(['products' => fn ($q) => $q->active()->with(['variants', 'images'])])
                ->firstOrFail();
        });

        return new CollectionResource($collection);
    }

    public function products(string $slug)
    {
        return $this->show($slug);
    }
}
