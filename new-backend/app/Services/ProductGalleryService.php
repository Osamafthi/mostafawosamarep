<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\DB;

class ProductGalleryService
{
    /**
     * Replace a product's gallery with the given URLs, preserving order.
     *
     * @param  array<int, string|null>  $urls
     */
    public function replace(Product $product, array $urls): void
    {
        DB::transaction(function () use ($product, $urls) {
            $product->images()->delete();

            $sort = 0;
            foreach ($urls as $url) {
                $url = is_string($url) ? trim($url) : '';
                if ($url === '') {
                    continue;
                }

                ProductImage::create([
                    'product_id' => $product->id,
                    'url' => $url,
                    'sort_order' => $sort++,
                ]);
            }
        });
    }
}
