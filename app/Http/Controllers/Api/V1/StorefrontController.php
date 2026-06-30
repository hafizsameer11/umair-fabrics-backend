<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\MediaUrl;
use App\Http\Resources\BundleResource;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\ProductResource;
use App\Models\AnnouncementBar;
use App\Models\Bundle;
use App\Models\Collection;
use App\Models\HeroSlide;
use App\Models\Menu;
use App\Models\Page;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Setting;
use App\Models\ShippingSetting;
use App\Services\CacheService;
use App\Services\ShippingService;

class StorefrontController extends Controller
{
    public function settings()
    {
        return CacheService::remember('api.storefront', 300, function () {
            $homepageSections = Collection::where('show_on_homepage', true)
                ->orderBy('sort_order')
                ->with(['products' => function ($q) {
                    $q->active()->with(['variants', 'images', 'companionProducts']);
                }])
                ->get()
                ->map(function (Collection $collection) {
                    $limit = max(1, min(48, (int) ($collection->homepage_product_limit ?: 8)));

                    return [
                        'id' => $collection->id,
                        'title' => $collection->homepage_title ?: $collection->name,
                        'subtitle' => $collection->description,
                        'type' => 'collection',
                        'collection_slug' => $collection->slug,
                        'product_limit' => $limit,
                        'products' => ProductResource::collection($collection->products->take($limit)),
                    ];
                });

            return [
                'store' => [
                    'name' => config('brand.name'),
                    'phone' => config('brand.phone'),
                    'email' => config('brand.email'),
                    'website' => config('brand.website'),
                    'logo' => Setting::get('store_logo')
                        ? MediaUrl::fromStoragePath(Setting::get('store_logo'))
                        : config('brand.logo'),
                    'whatsapp' => config('brand.whatsapp'),
                    'currency' => config('brand.currency'),
                ],
                'social' => config('brand.social'),
                'hero_slides' => HeroSlide::active()->with('collection')->get()->map(fn ($s) => [
                    'id' => $s->id,
                    'title' => $s->title ?: null,
                    'subtitle' => $s->subtitle,
                    'button_text' => $s->button_text,
                    'button_url' => $s->linkUrl(),
                    'image' => $s->imageUrl(),
                    'countdown_ends_at' => $s->countdown_ends_at?->toIso8601String(),
                ]),
                'announcements' => AnnouncementBar::where('is_active', true)->orderBy('sort_order')->get(['message', 'link']),
                'shipping' => app(ShippingService::class)->rulesForApi(
                    ShippingSetting::where('is_active', true)->first()
                ),
                'homepage_sections' => $homepageSections,
                'bundles' => BundleResource::collection(
                    Bundle::active()->withCount('products')
                        ->with(['products' => fn ($q) => $q->active()])
                        ->orderBy('sort_order')->limit(8)->get()
                ),
                'all_products' => ProductResource::collection(
                    Product::active()->with(['variants', 'images', 'companionProducts'])->latest()->limit(48)->get()
                ),
                'payment_methods' => PaymentMethod::where('is_enabled', true)->orderBy('sort_order')->get(['code', 'name', 'instructions']),
            ];
        });
    }

    public function menu(string $location)
    {
        $menu = CacheService::remember("api.menu.{$location}", 600, function () use ($location) {
            return Menu::where('location', $location)->with('items')->first();
        });

        if (! $menu) {
            return response()->json(['items' => []]);
        }

        return response()->json([
            'items' => $menu->items->map(fn ($i) => ['label' => $i->label, 'url' => $i->url]),
        ]);
    }

    public function page(string $slug)
    {
        $page = CacheService::remember("api.page.{$slug}", 600, function () use ($slug) {
            return Page::where('slug', $slug)->where('is_published', true)->firstOrFail();
        });

        return response()->json([
            'title' => $page->title,
            'slug' => $page->slug,
            'body_html' => $page->body_html,
            'meta_title' => $page->meta_title,
            'meta_description' => $page->meta_description,
        ]);
    }

    public function collections()
    {
        return CollectionController::class;
    }
}
