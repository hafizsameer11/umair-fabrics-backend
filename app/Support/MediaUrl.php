<?php

namespace App\Support;

class MediaUrl
{
    public static function fromStoragePath(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $path = trim($path);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $path = (string) preg_replace('#^https?://[^/]+/storage/#', '', $path);
        }

        $path = ltrim($path, '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }

        return asset('storage/'.$path);
    }
}
