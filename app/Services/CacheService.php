<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    public const STOREFRONT_TAG = 'storefront';

    public static function remember(string $key, int $ttl, callable $callback): mixed
    {
        return Cache::remember($key, $ttl, $callback);
    }

    public static function flushStorefront(): void
    {
        Cache::flush();
    }
}
