<?php

namespace App\Support;

use Closure;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

/**
 * Thin wrapper around Laravel's Cache used by the public catalog endpoints.
 *
 * The database cache driver has no tags, so we implement "group invalidation"
 * via a per-group version counter: every cache key embeds the current version
 * of its group (e.g. `catalog:products:v7:show:42`). Bumping the counter
 * (see flush()) orphans every existing key in the group in O(1); the orphans
 * expire on their own.
 *
 * Everything routes through the configured Laravel cache store, so switching
 * from `database` to `redis` later is purely an env change.
 */
class CatalogCache
{
    public static function enabled(): bool
    {
        return (bool) config('catalog_cache.enabled', true);
    }

    /**
     * Remember a value inside a group. On a miss, $callback is invoked and
     * its return value cached for $ttl seconds under a versioned key.
     *
     * When caching is disabled globally, the callback is always invoked.
     */
    public static function remember(string $group, string $key, int $ttl, Closure $callback): mixed
    {
        if (! self::enabled()) {
            return $callback();
        }

        $version = self::version($group);
        $fullKey = "catalog:{$group}:v{$version}:{$key}";

        return self::store()->remember($fullKey, $ttl, $callback);
    }

    /**
     * Invalidate every cached entry in a group by bumping its version counter.
     *
     * Safe to call from write paths unconditionally; a no-op when the cache
     * is disabled.
     */
    public static function flush(string $group): void
    {
        if (! self::enabled()) {
            return;
        }

        $versionKey = self::versionKey($group);
        $store = self::store();

        // Seed the counter on first use; increment() returns false on missing keys.
        $store->add($versionKey, 1, now()->addYears(10));
        $store->increment($versionKey);
    }

    /**
     * Build a stable cache sub-key from an associative array of filters. The
     * array is sorted so `{a:1,b:2}` and `{b:2,a:1}` hash to the same key.
     */
    public static function hashFilters(array $filters): string
    {
        ksort($filters);

        return md5((string) json_encode($filters));
    }

    private static function version(string $group): int
    {
        $versionKey = self::versionKey($group);
        $store = self::store();

        $version = $store->get($versionKey);

        if ($version === null) {
            $store->add($versionKey, 1, now()->addYears(10));
            $version = $store->get($versionKey) ?? 1;
        }

        return (int) $version;
    }

    private static function versionKey(string $group): string
    {
        return "catalog:{$group}:version";
    }

    private static function store(): Repository
    {
        $store = config('catalog_cache.store');

        return Cache::store($store);
    }
}
