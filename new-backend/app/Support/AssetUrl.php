<?php

namespace App\Support;

final class AssetUrl
{
    /**
     * Turn a stored image path into an absolute URL for API clients (mobile, etc.).
     * Already-absolute URLs are returned unchanged.
     *
     * Legacy XAMPP paths look like `/mostafawosama/assets/...`.
     * If PUBLIC_ASSET_URL or FRONTEND_URL includes `/mostafawosama`, we must not prefix that
     * twice — only the scheme+host (+ port) is used for those relatives.
     * Paths under `/storage/` are assumed to live on this Laravel app origin (APP_URL).
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

        $path = '/'.ltrim($trimmed, '/');

        if (str_starts_with($path, '/storage/')) {
            $base = rtrim((string) config('app.url'), '/');

            return $base.$path;
        }

        // Legacy static files (Apache docroot): /mostafawosama/...
        $origin = self::staticFilesOrigin();

        return $origin.'/'.ltrim($trimmed, '/');
    }

    /**
     * Origin (scheme://host[:port]) for storefront static files — never a path suffix.
     */
    private static function staticFilesOrigin(): string
    {
        $raw = trim((string) (config('app.public_asset_url') ?? ''));
        if ($raw === '') {
            $raw = (string) config('app.frontend_url', '');
        }
        $origin = self::parseOrigin($raw);
        if ($origin !== '') {
            return $origin;
        }

        return self::parseOrigin((string) config('app.url', 'http://localhost'));
    }

    private static function parseOrigin(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        $parts = parse_url($url);
        if ($parts === false || empty($parts['host'])) {
            return rtrim($url, '/');
        }
        $scheme = ($parts['scheme'] ?? 'http').'://';
        $host = $parts['host'];
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';

        return $scheme.$host.$port;
    }
}
