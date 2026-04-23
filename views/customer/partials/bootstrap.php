<?php
/**
 * Shared bootstrap for every customer-facing view.
 *
 * Exposes:
 *   - $basePath           application base URL prefix
 *   - $apiBase            public Laravel /api/v1 base URL
 *   - $assetVersion()     helper that cache-busts an asset path based on mtime
 *
 * NOTE: Only public values are sent to the client (base path + API URL).
 * No .env secrets, admin URLs, or tokens are referenced here.
 */

if (!isset($basePath)) {
    $basePath = '/mostafawosama';
}

// The Laravel API runs on its own PHP 8.3 dev server (`php artisan serve`)
// because this XAMPP ships PHP 8.2. This matches views/admin/*.php.
if (!isset($apiBase)) {
    $apiBase = 'http://localhost:8000/api/v1';
}

if (!function_exists('assetVersion')) {
    /**
     * Append ?v=<filemtime> to an asset path so cache is busted on changes.
     * @param string $relative path relative to the workspace root (e.g. "/assets/js/home.js")
     */
    function assetVersion(string $relative): string
    {
        $root = realpath(__DIR__ . '/../../..');
        $full = $root . $relative;
        return file_exists($full) ? (string) filemtime($full) : '1';
    }
}

if (!function_exists('customerAsset')) {
    function customerAsset(string $basePath, string $relative): string
    {
        return $basePath . $relative . '?v=' . assetVersion($relative);
    }
}
