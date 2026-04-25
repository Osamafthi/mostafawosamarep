<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\StoreDeliveryPersonRequest;
use App\Http\Requests\V1\Admin\UpdateDeliveryPersonRequest;
use App\Http\Resources\V1\DeliveryPersonResource;
use App\Models\DeliveryPerson;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryPersonController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = DeliveryPerson::query();

        // The order-detail drawer asks for `?active=1` to populate the
        // "assign to courier" dropdown so admins don't accidentally pick
        // a deactivated account.
        if ($request->query('active') !== null) {
            $query->where('is_active', filter_var($request->query('active'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($q = trim((string) $request->query('q', ''))) {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        $persons = $query->orderBy('name')->get();

        return ApiResponse::success(
            DeliveryPersonResource::collection($persons)->resolve()
        );
    }

    public function store(StoreDeliveryPersonRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['is_active'] = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true;

        $person = DeliveryPerson::create($data);

        return ApiResponse::created((new DeliveryPersonResource($person))->resolve());
    }

    public function show(int $id): JsonResponse
    {
        $person = DeliveryPerson::query()->find($id);

        if (! $person) {
            return ApiResponse::notFound('Delivery person not found');
        }

        return ApiResponse::success((new DeliveryPersonResource($person))->resolve());
    }

    public function update(UpdateDeliveryPersonRequest $request, int $id): JsonResponse
    {
        $person = DeliveryPerson::query()->find($id);

        if (! $person) {
            return ApiResponse::notFound('Delivery person not found');
        }

        $data = $request->validated();

        // Empty / missing password means "leave password unchanged".
        // The cast `password => hashed` re-hashes the value on save.
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $person->fill($data);
        $person->save();

        // Deactivating revokes existing tokens so the courier is signed
        // out immediately and cannot keep operating with a stale token.
        if (isset($data['is_active']) && $data['is_active'] === false) {
            $person->tokens()->delete();
        }

        return ApiResponse::success((new DeliveryPersonResource($person))->resolve());
    }

    public function toggleActive(int $id): JsonResponse
    {
        $person = DeliveryPerson::query()->find($id);

        if (! $person) {
            return ApiResponse::notFound('Delivery person not found');
        }

        $person->is_active = ! $person->is_active;
        $person->save();

        if (! $person->is_active) {
            $person->tokens()->delete();
        }

        return ApiResponse::success((new DeliveryPersonResource($person))->resolve());
    }

    public function destroy(int $id): JsonResponse
    {
        $person = DeliveryPerson::query()->find($id);

        if (! $person) {
            return ApiResponse::notFound('Delivery person not found');
        }

        // Orders keep their snapshot of the assigned courier id thanks
        // to the FK ON DELETE SET NULL, so historical orders stay
        // readable but lose the link.
        $person->tokens()->delete();
        $person->delete();

        return ApiResponse::success(['deleted' => true]);
    }
}
