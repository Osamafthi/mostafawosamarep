<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PaginatedCollection;
use App\Http\Resources\V1\ProductResource;
use App\Models\Product;
use App\Support\ApiResponse;
use App\Support\CatalogCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query('page', 1));
        $limit = min(100, max(1, (int) $request->query('limit', 20)));
        $q = trim((string) $request->query('q', ''));
        $categoryId = $request->query('category_id');
        $status = $request->query('status');

        $filters = [
            'page' => $page,
            'limit' => $limit,
            'category_id' => $categoryId !== null ? (int) $categoryId : null,
            'status' => $status,
        ];

        // Only cache "hot" requests — first few pages with no free-text search,
        // which is what the storefront home strips and category pages request.
        // Search combos explode the key space and aren't worth caching.
        $cacheable = ($q === '') && ($page <= 3);

        $build = function () use ($page, $limit, $q, $categoryId, $status) {
            $query = Product::query()->with('category');

            if ($q !== '') {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            }

            if ($categoryId) {
                $query->where('category_id', (int) $categoryId);
            }

            if ($status) {
                $query->where('status', $status);
            } else {
                $query->active();
            }

            $paginator = $query->orderByDesc('id')->paginate($limit, ['*'], 'page', $page);

            return PaginatedCollection::toArray($paginator, ProductResource::class);
        };

        if ($cacheable) {
            $data = CatalogCache::remember(
                'products',
                'list:' . CatalogCache::hashFilters($filters),
                (int) config('catalog_cache.ttl.product_list', 60),
                $build
            );
        } else {
            $data = $build();
        }

        return ApiResponse::success($data);
    }

    public function show(int $id): JsonResponse
    {
        $data = CatalogCache::remember(
            'products',
            "show:{$id}",
            (int) config('catalog_cache.ttl.product_show', 300),
            function () use ($id) {
                $product = Product::query()->with(['category', 'images'])->find($id);

                if (! $product) {
                    return null;
                }

                return (new ProductResource($product))->resolve();
            }
        );

        if ($data === null) {
            return ApiResponse::notFound('Product not found');
        }

        return ApiResponse::success($data);
    }
}
