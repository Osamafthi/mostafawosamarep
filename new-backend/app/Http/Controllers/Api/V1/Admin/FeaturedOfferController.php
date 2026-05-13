<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeaturedOffer;
use App\Models\Product;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeaturedOfferController extends Controller
{
    public function index(): JsonResponse
    {
        $offers = FeaturedOffer::with('product.category')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return ApiResponse::success($offers);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $validated['is_active'] ?? true;

        // Check if product is already featured
        $existing = FeaturedOffer::where('product_id', $validated['product_id'])->first();
        if ($existing) {
            return ApiResponse::error(
                'Product is already featured',
                422,
                ['product_id' => ['This product is already in featured offers']]
            );
        }

        $offer = FeaturedOffer::create($validated);
        $offer->load('product.category');

        return ApiResponse::created($offer);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $offer = FeaturedOffer::find($id);

        if (! $offer) {
            return ApiResponse::notFound('Featured offer not found');
        }

        $validated = $request->validate([
            'sort_order' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $offer->update($validated);
        $offer->load('product.category');

        return ApiResponse::success($offer);
    }

    public function destroy(int $id): JsonResponse
    {
        $offer = FeaturedOffer::find($id);

        if (! $offer) {
            return ApiResponse::notFound('Featured offer not found');
        }

        $offer->delete();

        return ApiResponse::success(['message' => 'Featured offer removed']);
    }

    public function replace(Request $request, int $id): JsonResponse
    {
        $offer = FeaturedOffer::find($id);

        if (! $offer) {
            return ApiResponse::notFound('Featured offer not found');
        }

        $validated = $request->validate([
            'new_product_id' => 'required|integer|exists:products,id',
        ]);

        // Check if new product is already featured in another offer
        $existing = FeaturedOffer::where('product_id', $validated['new_product_id'])
            ->where('id', '!=', $id)
            ->first();

        if ($existing) {
            return ApiResponse::error(
                'New product is already featured',
                422,
                ['new_product_id' => ['This product is already in featured offers']]
            );
        }

        $offer->update(['product_id' => $validated['new_product_id']]);
        $offer->load('product.category');

        return ApiResponse::success([
            'message' => 'Product replaced successfully',
            'offer' => $offer,
        ]);
    }
}
