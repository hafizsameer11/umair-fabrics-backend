<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BundleResource;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\ProductResource;
use App\Models\AnnouncementBar;
use App\Models\Bundle;
use App\Models\HeroSlide;
use App\Models\HomepageSection;
use App\Models\Menu;
use App\Models\Page;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Setting;
use App\Models\ShippingSetting;
use App\Services\CacheService;

class StorefrontController extends Controller
{
    public function settings()
    {
        return CacheService::remember('api.storefront', 300, function () {
            $sections = HomepageSection::where('is_active', true)
                ->orderBy('sort_order')
                ->with(['collection.products' => function ($q) {
                    $q->active()->with(['variants', 'images', 'companionProducts'])->limit(12);
                }])
                ->get()
                ->map(function ($section) {
                    $products = collect();

                    if ($section->type === 'collection' && $section->collection) {
                        $products = $section->collection->products->take($section->product_limit);
                    } elseif ($section->type === 'featured_products') {
                        $products = Product::active()->where('featured', true)
                            ->with(['variants', 'images', 'companionProducts'])
                            ->limit($section->product_limit)
                            ->get();
                    }

                    return [
                        'id' => $section->id,
                        'title' => $section->title,
                        'subtitle' => $section->subtitle,
                        'type' => $section->type,
                        'collection_slug' => $section->collection?->slug,
                        'products' => ProductResource::collection($products),
                    ];
                });

            return [
                'store' => [
                    'name' => config('brand.name'),
                    'phone' => config('brand.phone'),
                    'email' => config('brand.email'),
                    'website' => config('brand.website'),
                    'logo' => Setting::get('store_logo')
                        ? asset('storage/'.Setting::get('store_logo'))
                        : config('brand.logo'),
                    'whatsapp' => config('brand.whatsapp'),
                    'currency' => config('brand.currency'),
                ],
                'social' => config('brand.social'),
                'hero_slides' => HeroSlide::active()->get()->map(fn ($s) => [
                    'id' => $s->id,
                    'title' => $s->title,
                    'subtitle' => $s->subtitle,
                    'button_text' => $s->button_text,
                    'button_url' => $s->button_url,
                    'image' => $s->imageUrl(),
                ]),
                'announcements' => AnnouncementBar::where('is_active', true)->orderBy('sort_order')->get(['message', 'link']),
                'shipping' => ShippingSetting::where('is_active', true)->first(),
                'homepage_sections' => $sections,
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
