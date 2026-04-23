<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Catalog cache master switch
    |--------------------------------------------------------------------------
    |
    | Global kill-switch for the storefront catalog cache (categories, product
    | listings, product detail). When false, every read hits the database.
    |
    */

    'enabled' => env('CATALOG_CACHE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Cache store
    |--------------------------------------------------------------------------
    |
    | Which Laravel cache store to use. Leave null to inherit the app default
    | (see config/cache.php + CACHE_STORE). Set CATALOG_CACHE_STORE=redis in
    | production if you want the catalog cache on Redis while keeping other
    | subsystems on the default store.
    |
    */

    'store' => env('CATALOG_CACHE_STORE'),

    /*
    |--------------------------------------------------------------------------
    | TTLs (seconds)
    |--------------------------------------------------------------------------
    |
    | Kept short on purpose: admin writes flush the affected group, but the
    | TTL is the upper bound on how stale a read can ever be if a flush is
    | missed (e.g. stock decremented by a failing order path).
    |
    */

    'ttl' => [
        'categories'   => (int) env('CATALOG_CACHE_TTL_CATEGORIES', 600),
        'product_list' => (int) env('CATALOG_CACHE_TTL_LIST', 60),
        'product_show' => (int) env('CATALOG_CACHE_TTL_SHOW', 300),
    ],

];
