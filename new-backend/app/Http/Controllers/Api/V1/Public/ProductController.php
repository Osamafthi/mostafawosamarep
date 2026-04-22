<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PaginatedCollection;
use App\Http\Resources\V1\ProductResource;
use App\Models\Product;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query('page', 1));
        $limit = min(100, max(1, (int) $request->query('limit', 20)));

        $query = Product::query()->with('category');

        if ($q = trim((string) $request->query('q', ''))) {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', (int) $categoryId);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        } else {
            // Public listing only exposes active products.
            $query->active();
        }

        $paginator = $query->orderByDesc('id')->paginate($limit, ['*'], 'page', $page);

        return ApiResponse::success(
            PaginatedCollection::toArray($paginator, ProductResource::class)
        );
    }

    public function show(int $id): JsonResponse
    {
        $product = Product::query()->with(['category', 'images'])->find($id);

        if (! $product) {
            return ApiResponse::notFound('Product not found');
        }

        return ApiResponse::success((new ProductResource($product))->resolve());
    }
}
