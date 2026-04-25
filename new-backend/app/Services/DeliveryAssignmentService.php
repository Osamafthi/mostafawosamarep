<?php

namespace App\Services;

use App\Models\DeliveryPerson;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * Single-entry-point service for picking the courier who should handle a
 * given order. Intentionally tiny so future changes — capacity caps,
 * self-claim, geo-routing, anti-hoarding — plug into one place without
 * touching controllers, the placement service, or the frontend.
 *
 * MVP strategy: FIFO over active couriers, ordered by oldest
 * `last_assigned_at` (NULL sorted first, treated as the epoch).
 *
 * Concurrency: callers MUST invoke this from inside a DB transaction
 * (OrderPlacementService::place wraps the whole order create) so the
 * `lockForUpdate` row lock prevents two simultaneous orders from picking
 * the same courier and skewing the rotation.
 */
class DeliveryAssignmentService
{
    /**
     * Assign a courier to the given order.
     *
     * Returns the picked DeliveryPerson (already updated to bump
     * `last_assigned_at`) or null if no active courier is available — in
     * which case the caller should leave `delivery_person_id = null` so
     * an admin can assign manually later.
     */
    public function assignFor(Order $order): ?DeliveryPerson
    {
        // IFNULL pushes never-assigned couriers to the top so a brand-new
        // active courier picks up the next order immediately.
        $picked = DeliveryPerson::query()
            ->where('is_active', true)
            ->orderByRaw('IFNULL(last_assigned_at, "1970-01-01 00:00:00") asc')
            ->orderBy('id', 'asc')
            ->lockForUpdate()
            ->first();

        if (! $picked) {
            return null;
        }

        $picked->forceFill(['last_assigned_at' => now()])->save();

        $order->delivery_person_id = $picked->getKey();

        return $picked;
    }
}
