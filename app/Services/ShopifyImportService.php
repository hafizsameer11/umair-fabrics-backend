<?php

namespace App\Services;

use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ShopifyImportService
{
    public function importFromStore(string $storeUrl, int $limit = 50, bool $downloadImages = false): int
    {
        $baseUrl = rtrim($storeUrl, '/');
        $imported = 0;
        $page = 1;

        while ($imported < $limit) {
            $response = Http::timeout(30)->get("{$baseUrl}/products.json", [
                'limit' => min(50, $limit - $imported),
                'page' => $page,
            ]);

            if (! $response->successful()) {
                break;
            }

            $products = $response->json('products', []);

            if (empty($products)) {
                break;
            }

            foreach ($products as $shopifyProduct) {
                if ($imported >= $limit) {
                    break;
                }

                $this->importProduct($shopifyProduct, $downloadImages);
                $imported++;
            }

            $page++;
        }

        return $imported;
    }

    public function importProduct(array $data, bool $downloadImages = false): Product
    {
        $slug = $data['handle'] ?? Str::slug($data['title']);
        $slug = $this->uniqueSlug($slug);

        $product = Product::updateOrCreate(
            ['slug' => $slug],
            [
                'title' => $data['title'],
                'description_html' => $data['body_html'] ?? '',
                'vendor' => $data['vendor'] ?? null,
                'product_type' => $data['product_type'] ?? null,
                'tags' => Str::limit(
                    is_array($data['tags'] ?? null) ? implode(',', $data['tags']) : ($data['tags'] ?? ''),
                    500
                ),
                'status' => 'active',
                'meta_title' => $data['title'],
            ]
        );

        $product->variants()->delete();
        $product->options()->each(fn ($o) => $o->values()->delete());
        $product->options()->delete();
        $product->images()->delete();

        foreach ($data['options'] ?? [] as $i => $option) {
            if (($option['name'] ?? '') === 'Title' && count($option['values'] ?? []) === 1 && ($option['values'][0] ?? '') === 'Default Title') {
                continue;
            }

            $opt = ProductOption::create([
                'product_id' => $product->id,
                'name' => $option['name'],
                'position' => $i + 1,
            ]);

            foreach ($option['values'] ?? [] as $j => $value) {
                ProductOptionValue::create([
                    'product_option_id' => $opt->id,
                    'value' => $value,
                    'position' => $j + 1,
                ]);
            }
        }

        foreach ($data['variants'] ?? [] as $i => $variant) {
            ProductVariant::create([
                'product_id' => $product->id,
                'title' => $variant['title'] !== 'Default Title' ? $variant['title'] : null,
                'sku' => $variant['sku'] ?? null,
                'price' => $variant['price'] ?? 0,
                'compare_at_price' => $variant['compare_at_price'] ?? null,
                'stock' => ($variant['available'] ?? true) ? 10 : 0,
                'option1' => $variant['option1'] !== 'Default Title' ? $variant['option1'] : null,
                'option2' => $variant['option2'] ?? null,
                'option3' => $variant['option3'] ?? null,
                'position' => $i + 1,
            ]);
        }

        foreach ($data['images'] ?? [] as $i => $image) {
            $path = $image['src'];

            if ($downloadImages) {
                $path = $this->downloadImage($image['src'], $product->slug, $i);
            }

            if ($path) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $path,
                    'alt' => $image['alt'] ?? $product->title,
                    'sort_order' => $i,
                ]);
            }
        }

        return $product->fresh(['variants', 'images']);
    }

    private function downloadImage(string $url, string $productSlug, int $index): ?string
    {
        try {
            $response = Http::timeout(30)->get($url);

            if (! $response->successful()) {
                return null;
            }

            $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $filename = "products/{$productSlug}-{$index}.{$extension}";

            Storage::disk('public')->put($filename, $response->body());

            return $filename;
        } catch (\Throwable) {
            return null;
        }
    }

    private function uniqueSlug(string $slug): string
    {
        $original = $slug;
        $counter = 1;

        while (Product::where('slug', $slug)->exists()) {
            $slug = "{$original}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
