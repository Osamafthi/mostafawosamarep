<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CustomerResource;
use App\Http\Resources\V1\OrderResource;
use App\Http\Resources\V1\PaginatedCollection;
use App\Models\Customer;
use App\Models\Order;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user();

        return ApiResponse::success((new CustomerResource($customer))->resolve());
    }

    /**
     * Allowed values for `?window=` — kept narrow so the storefront
     * can't pass arbitrary intervals.
     */
    private const WINDOWS = ['6m', '1y', 'all'];

    /**
     * Allowed values for `?status=` — mirrors the enum on the `orders`
     * table. `''` / missing means "any status".
     */
    private const STATUSES = ['pending', 'processing', 'delivered', 'cancelled'];

    public function orders(Request $request): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user();

        $page = max(1, (int) $request->query('page', 1));
        $limit = min(100, max(1, (int) $request->query('limit', 10)));

        $window = (string) $request->query('window', '6m');
        if (! in_array($window, self::WINDOWS, true)) {
            $window = '6m';
        }

        $status = (string) $request->query('status', '');
        if ($status !== '' && ! in_array($status, self::STATUSES, true)) {
            $status = '';
        }

        $query = Order::query()->where('customer_id', $customer->getKey());

        // Default to "last 6 months" so long-time customers don't get
        // hundreds of rows loaded on the first page hit.
        if ($window === '6m') {
            $query->where('created_at', '>=', now()->subMonths(6));
        } elseif ($window === '1y') {
            $query->where('created_at', '>=', now()->subYear());
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $paginator = $query
            ->orderByDesc('id')
            ->paginate($limit, ['*'], 'page', $page);

        return ApiResponse::success(
            PaginatedCollection::toArray($paginator, OrderResource::class)
        );
    }

    public function showOrder(Request $request, int $id): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user();

        $order = Order::query()
            ->with(['items.product'])
            ->where('customer_id', $customer->getKey())
            ->find($id);

        if (! $order) {
            return ApiResponse::notFound('Order not found');
        }

        return ApiResponse::success((new OrderResource($order))->resolve());
    }
}
