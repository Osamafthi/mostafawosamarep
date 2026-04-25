<?php
/**
 * Shared bootstrap for every courier-facing view.
 *
 * Mirrors views/customer/partials/bootstrap.php intentionally so the
 * three role panels stay symmetrical. Only public values are sent to
 * the client (base path + API URL); never tokens or secrets.
 */

if (!isset($basePath)) {
    $basePath = '/mostafawosama';
}

if (!isset($apiBase)) {
    $apiBase = 'http://localhost:8000/api/v1';
}

if (!function_exists('assetVersion')) {
    function assetVersion(string $relative): string
    {
        $root = realpath(__DIR__ . '/../../..');
        $full = $root . $relative;
        return file_exists($full) ? (string) filemtime($full) : '1';
    }
}

if (!function_exists('deliveryAsset')) {
    function deliveryAsset(string $basePath, string $relative): string
    {
        return $basePath . $relative . '?v=' . assetVersion($relative);
    }
}
