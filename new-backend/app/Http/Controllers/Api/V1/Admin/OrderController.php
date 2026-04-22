<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Order\UpdateOrderStatusRequest;
use App\Http\Requests\V1\Order\UpdatePaymentStatusRequest;
use App\Http\Resources\V1\OrderResource;
use App\Http\Resources\V1\PaginatedCollection;
use App\Models\Order;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query('page', 1));
        $limit = min(100, max(1, (int) $request->query('limit', 20)));

        $query = Order::query();

        if ($q = trim((string) $request->query('q', ''))) {
            $query->where(function ($sub) use ($q) {
                $sub->where('order_number', 'like', "%{$q}%")
                    ->orWhere('customer_email', 'like', "%{$q}%")
                    ->orWhere('customer_name', 'like', "%{$q}%");
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($paymentStatus = $request->query('payment_status')) {
            $query->where('payment_status', $paymentStatus);
        }

        $paginator = $query->orderByDesc('id')->paginate($limit, ['*'], 'page', $page);

        return ApiResponse::success(
            PaginatedCollection::toArray($paginator, OrderResource::class)
        );
    }

    public function show(int $id): JsonResponse
    {
        $order = Order::query()->with(['items.product'])->find($id);

        if (! $order) {
            return ApiResponse::notFound('Order not found');
        }

        return ApiResponse::success((new OrderResource($order))->resolve());
    }

    public function updateStatus(UpdateOrderStatusRequest $request, int $id): JsonResponse
    {
        $order = Order::query()->find($id);

        if (! $order) {
            return ApiResponse::notFound('Order not found');
        }

        $order->status = $request->validated()['status'];
        $order->save();

        $order->load('items.product');

        return ApiResponse::success((new OrderResource($order))->resolve());
    }

    public function updatePaymentStatus(UpdatePaymentStatusRequest $request, int $id): JsonResponse
    {
        $order = Order::query()->find($id);

        if (! $order) {
            return ApiResponse::notFound('Order not found');
        }

        $order->payment_status = $request->validated()['payment_status'];
        $order->save();

        $order->load('items.product');

        return ApiResponse::success((new OrderResource($order))->resolve());
    }

    public function stats(): JsonResponse
    {
        $revenueStatuses = ['processing', 'shipped', 'delivered'];

        return ApiResponse::success([
            'total_orders' => Order::query()->count(),
            'pending' => Order::query()->where('status', 'pending')->count(),
            'processing' => Order::query()->where('status', 'processing')->count(),
            'shipped' => Order::query()->where('status', 'shipped')->count(),
            'delivered' => Order::query()->where('status', 'delivered')->count(),
            'cancelled' => Order::query()->where('status', 'cancelled')->count(),
            'revenue' => (float) Order::query()->whereIn('status', $revenueStatuses)->sum('total'),
        ]);
    }
}
