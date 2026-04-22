<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Product\StoreProductRequest;
use App\Http\Requests\V1\Product\UpdateProductRequest;
use App\Http\Resources\V1\PaginatedCollection;
use App\Http\Resources\V1\ProductResource;
use App\Models\Order;
use App\Models\Product;
use App\Services\ProductGalleryService;
use App\Support\ApiResponse;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private ProductGalleryService $gallery)
    {
    }

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

    public function store(StoreProductRequest $request): JsonResponse
    {
        $data = $request->validated();

        $gallery = $data['gallery'] ?? null;
        unset($data['gallery']);

        $product = Product::create($data);

        if (is_array($gallery)) {
            $this->gallery->replace($product, $gallery);
        }

        $product->load(['category', 'images']);

        return ApiResponse::created((new ProductResource($product))->resolve());
    }

    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = Product::query()->find($id);

        if (! $product) {
            return ApiResponse::notFound('Product not found');
        }

        $data = $request->validated();

        $gallery = array_key_exists('gallery', $data) ? $data['gallery'] : null;
        unset($data['gallery']);

        // Only update provided fields (keep legacy partial-update semantics).
        $product->fill(array_filter($data, fn ($v) => $v !== null || array_key_exists('discount_price', $data)));

        // Apply nullable discount_price explicitly when present.
        if (array_key_exists('discount_price', $data)) {
            $product->discount_price = $data['discount_price'];
        }

        $product->save();

        if (is_array($gallery)) {
            $this->gallery->replace($product, $gallery);
        }

        $product->load(['category', 'images']);

        return ApiResponse::success((new ProductResource($product))->resolve());
    }

    public function destroy(int $id): JsonResponse
    {
        $product = Product::query()->find($id);

        if (! $product) {
            return ApiResponse::notFound('Product not found');
        }

        try {
            $product->delete();
        } catch (QueryException $e) {
            return ApiResponse::error(
                'Product cannot be deleted because it is referenced by existing orders.',
                409
            );
        }

        return ApiResponse::success(['deleted' => true]);
    }

    public function stats(): JsonResponse
    {
        $productStats = [
            'total_products' => Product::query()->count(),
            'active_products' => Product::query()->where('status', 'active')->count(),
            'inactive_products' => Product::query()->where('status', 'inactive')->count(),
            'total_categories' => \App\Models\Category::query()->count(),
            'low_stock_products' => Product::query()->where('stock', '<=', 5)->count(),
        ];

        $revenueStatuses = ['processing', 'shipped', 'delivered'];

        $orderStats = [
            'total_orders' => Order::query()->count(),
            'pending' => Order::query()->where('status', 'pending')->count(),
            'processing' => Order::query()->where('status', 'processing')->count(),
            'shipped' => Order::query()->where('status', 'shipped')->count(),
            'delivered' => Order::query()->where('status', 'delivered')->count(),
            'cancelled' => Order::query()->where('status', 'cancelled')->count(),
            'revenue' => (float) Order::query()->whereIn('status', $revenueStatuses)->sum('total'),
        ];

        return ApiResponse::success([
            'products' => $productStats,
            'orders' => $orderStats,
        ]);
    }
}
