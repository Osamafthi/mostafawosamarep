<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Order\StoreOrderRequest;
use App\Http\Resources\V1\OrderResource;
use App\Models\Customer;
use App\Services\OrderPlacementService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderPlacementService $orders)
    {
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $data = $request->validated();

        // If the caller presents a Sanctum token with the `customer` ability,
        // associate the order with that customer. Guest checkout is still allowed.
        $customer = $this->resolveAuthenticatedCustomer($request);

        $order = $this->orders->place($data, $customer);

        return ApiResponse::created((new OrderResource($order))->resolve());
    }

    protected function resolveAuthenticatedCustomer(Request $request): ?Customer
    {
        $user = $request->user('sanctum');

        if ($user instanceof Customer && $user->tokenCan('customer')) {
            return $user;
        }

        return null;
    }
}
