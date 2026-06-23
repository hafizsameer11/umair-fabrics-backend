<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RevalidateService
{
    public function revalidate(array $paths = [], array $tags = []): void
    {
        $frontendUrl = config('app.frontend_url');
        $secret = config('app.revalidate_secret');

        if (! $frontendUrl || ! $secret) {
            return;
        }

        try {
            Http::timeout(5)->post("{$frontendUrl}/api/revalidate", [
                'secret' => $secret,
                'paths' => $paths,
                'tags' => $tags,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to revalidate frontend: '.$e->getMessage());
        }
    }

    public function revalidateProduct(string $slug): void
    {
        $this->revalidate([
            '/',
            '/products/'.$slug,
            '/collections',
        ]);
    }
}
