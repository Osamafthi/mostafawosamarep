<?php

namespace App\Http\Controllers\Api\V1\Delivery;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Order\UpdateOrderStatusRequest;
use App\Http\Resources\V1\OrderResource;
use App\Http\Resources\V1\PaginatedCollection;
use App\Models\DeliveryPerson;
use App\Models\Order;
use App\Services\OrderStatusTransitionService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderStatusTransitionService $transitions)
    {
    }

    /**
     * Filters surfaced to the courier UI. `active` covers everything that
     * still needs delivery work; `delivered` is the historical archive;
     * `all` shows both. Anything else is ignored to avoid letting the
     * client poke at arbitrary statuses.
     */
    private const FILTERS = ['active', 'delivered', 'all'];

    public function index(Request $request): JsonResponse
    {
        /** @var DeliveryPerson $person */
        $person = $request->user();

        $page = max(1, (int) $request->query('page', 1));
        $limit = min(100, max(1, (int) $request->query('limit', 20)));

        $filter = (string) $request->query('filter', 'active');
        if (! in_array($filter, self::FILTERS, true)) {
            $filter = 'active';
        }

        $query = Order::query()
            ->with(['items.product', 'deliveryPerson'])
            ->where('delivery_person_id', $person->getKey());

        if ($filter === 'active') {
            $query->whereIn('status', ['pending', 'processing', 'shipped']);
        } elseif ($filter === 'delivered') {
            $query->where('status', 'delivered');
        }

        // Active orders sort newest-first so the courier sees fresh work
        // at the top; the historical view sorts the same way for
        // consistency.
        $paginator = $query
            ->orderByDesc('id')
            ->paginate($limit, ['*'], 'page', $page);

        return ApiResponse::success(
            PaginatedCollection::toArray($paginator, OrderResource::class)
        );
    }

    public function show(Request $request, int $id): JsonResponse
    {
        /** @var DeliveryPerson $person */
        $person = $request->user();

        // Scope by ownership FIRST so a courier cannot read someone
        // else's order by guessing the id in the URL.
        $order = Order::query()
            ->with(['items.product', 'deliveryPerson'])
            ->where('delivery_person_id', $person->getKey())
            ->find($id);

        if (! $order) {
            return ApiResponse::notFound('Order not found');
        }

        return ApiResponse::success((new OrderResource($order))->resolve());
    }

    public function updateStatus(UpdateOrderStatusRequest $request, int $id): JsonResponse
    {
        /** @var DeliveryPerson $person */
        $person = $request->user();

        $order = Order::query()
            ->where('delivery_person_id', $person->getKey())
            ->find($id);

        if (! $order) {
            return ApiResponse::notFound('Order not found');
        }

        $this->transitions->transition(
            $order,
            $request->validated()['status'],
            OrderStatusTransitionService::ACTOR_DELIVERY,
        );

        $order->load(['items.product', 'deliveryPerson']);

        return ApiResponse::success((new OrderResource($order))->resolve());
    }
}
