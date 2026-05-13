<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ProductResource;
use App\Services\OfferCalculationService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function __construct(
        private readonly OfferCalculationService $offerService
    ) {}

    /**
     * Get the best calculated offers based on discount percentage.
     */
    public function best(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 12);
        $limit = min(max((int) $limit, 1), 50);

        $products = $this->offerService->getBestOffers($limit);

        // Calculate discount percentages for each product
        $productsWithDiscount = $products->map(function ($product) {
            $percentage = $this->offerService->calculateDiscountPercentage($product);
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'price' => $product->price,
                'discount_price' => $product->discount_price,
                'discount_percentage' => $percentage,
                'stock' => $product->stock,
                'image_url' => $product->image_url,
                'status' => $product->status,
                'category' => $product->category,
            ];
        });

        return ApiResponse::success($productsWithDiscount);
    }

    /**
     * Get admin-curated featured offers.
     */
    public function featured(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 12);
        $limit = min(max((int) $limit, 1), 50);

        $featured = $this->offerService->getFeaturedOffers($limit);

        // Map featured offers with product details and discount percentages
        $offersWithDiscount = $featured->map(function ($offer) {
            $product = $offer->product;
            $percentage = $product ? $this->offerService->calculateDiscountPercentage($product) : null;

            return [
                'id' => $offer->id,
                'sort_order' => $offer->sort_order,
                'product' => $product ? [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'description' => $product->description,
                    'price' => $product->price,
                    'discount_price' => $product->discount_price,
                    'discount_percentage' => $percentage,
                    'stock' => $product->stock,
                    'image_url' => $product->image_url,
                    'status' => $product->status,
                    'category' => $product->category,
                ] : null,
            ];
        });

        return ApiResponse::success($offersWithDiscount);
    }
}
