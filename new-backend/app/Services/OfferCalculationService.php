<?php

namespace App\Services;

use App\Models\FeaturedOffer;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class OfferCalculationService
{
    /**
     * Get products with the best discount percentages.
     * Sorted by discount percentage (highest first).
     *
     * @param int $limit Maximum number of offers to return
     * @return Collection<Product>
     */
    public function getBestOffers(int $limit = 10): Collection
    {
        return Product::active()
            ->whereNotNull('discount_price')
            ->where('discount_price', '>', 0)
            ->whereColumn('discount_price', '<', 'price')
            ->orderByRaw('((price - discount_price) / price) DESC')
            ->limit($limit)
            ->with('category')
            ->get();
    }

    /**
     * Get admin-curated featured offers.
     * Returns active featured offers sorted by sort_order.
     *
     * @param int $limit Maximum number of featured offers to return
     * @return Collection<FeaturedOffer>
     */
    public function getFeaturedOffers(int $limit = 10): Collection
    {
        return FeaturedOffer::active()
            ->orderBy('sort_order')
            ->limit($limit)
            ->with('product.category')
            ->get();
    }

    /**
     * Calculate the discount percentage for a product.
     *
     * @param Product $product
     * @return float|null Returns null if no discount, otherwise percentage (0-100)
     */
    public function calculateDiscountPercentage(Product $product): ?float
    {
        if ($product->discount_price === null || $product->discount_price <= 0) {
            return null;
        }

        if ($product->discount_price >= $product->price) {
            return null;
        }

        $discount = $product->price - $product->discount_price;
        $percentage = ($discount / $product->price) * 100;

        return round($percentage, 1);
    }
}
