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

    public function orders(Request $request): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user();

        $page = max(1, (int) $request->query('page', 1));
        $limit = min(100, max(1, (int) $request->query('limit', 20)));

        $paginator = Order::query()
            ->where('customer_id', $customer->getKey())
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
