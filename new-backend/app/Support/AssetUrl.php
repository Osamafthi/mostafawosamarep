<?php

namespace App\Support;

final class AssetUrl
{
    /**
     * Turn a stored image path into an absolute URL for API clients (mobile, etc.).
     * Already-absolute URLs are returned unchanged.
     */
    public static function absolutize(?string $stored): ?string
    {
        if ($stored === null || $stored === '') {
            return null;
        }

        $trimmed = trim($stored);
        if (preg_match('#^https?://#i', $trimmed)) {
            return $trimmed;
        }

        $base = rtrim((string) config('app.public_asset_url', config('app.url')), '/');

        return $base.'/'.ltrim($trimmed, '/');
    }
}
