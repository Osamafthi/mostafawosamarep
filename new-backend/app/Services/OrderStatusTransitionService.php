<?php

namespace App\Services;

use App\Exceptions\InvalidOrderTransitionException;
use App\Models\Order;

/**
 * Single source of truth for order status transitions.
 *
 * The admin actor preserves the existing free-form behaviour (any status
 * can move to any status) so the existing admin UI keeps working. The
 * delivery actor is restricted to a small set of transitions and only on
 * orders assigned to them — that ownership check is enforced by the
 * controller before this service is called.
 */
class OrderStatusTransitionService
{
    public const ACTOR_ADMIN = 'admin';
    public const ACTOR_DELIVERY = 'delivery';

    /**
     * Transitions a delivery person is allowed to perform on their own
     * orders. Keys = current status, values = list of allowed new statuses.
     *
     *  - pending|processing -> shipped: courier picks the order up.
     *  - shipped -> delivered: courier hands it over.
     *  - shipped -> processing: rollback after a failed delivery attempt
     *    so the admin can decide what to do (retry, cancel, refund).
     */
    private const DELIVERY_TRANSITIONS = [
        'pending' => ['shipped'],
        'processing' => ['shipped'],
        'shipped' => ['delivered', 'processing'],
        'delivered' => [],
        'cancelled' => [],
    ];

    /**
     * Apply a status change. Persists the order on success.
     *
     * @throws InvalidOrderTransitionException
     */
    public function transition(Order $order, string $newStatus, string $actor): Order
    {
        if (! in_array($newStatus, Order::STATUSES, true)) {
            throw new InvalidOrderTransitionException("Unknown order status: {$newStatus}.");
        }

        $current = (string) $order->status;

        if ($current === $newStatus) {
            return $order;
        }

        if ($actor === self::ACTOR_ADMIN) {
            // Admin keeps full control — no transition matrix enforced
            // here so existing admin workflows aren't broken.
            $order->status = $newStatus;
            $order->save();

            return $order;
        }

        if ($actor === self::ACTOR_DELIVERY) {
            $allowed = self::DELIVERY_TRANSITIONS[$current] ?? [];

            if (! in_array($newStatus, $allowed, true)) {
                throw new InvalidOrderTransitionException(
                    "You cannot move an order from '{$current}' to '{$newStatus}'."
                );
            }

            $order->status = $newStatus;
            $order->save();

            return $order;
        }

        throw new InvalidOrderTransitionException("Unknown actor: {$actor}.");
    }
}
