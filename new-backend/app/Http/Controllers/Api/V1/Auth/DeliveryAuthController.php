<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Auth\DeliveryLoginRequest;
use App\Http\Resources\V1\DeliveryPersonResource;
use App\Models\DeliveryPerson;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DeliveryAuthController extends Controller
{
    public function login(DeliveryLoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $person = DeliveryPerson::query()->where('email', $credentials['email'])->first();

        if (! $person || ! Hash::check($credentials['password'], $person->password)) {
            return ApiResponse::unauthorized('Invalid credentials');
        }

        // Inactive accounts are blocked at the gate so a deactivated
        // courier cannot mint a fresh token even with the right password.
        if (! $person->is_active) {
            return ApiResponse::forbidden('Your account is currently inactive. Contact an admin.');
        }

        $token = $person->createToken('delivery-api', ['delivery'])->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'token_type' => 'Bearer',
            'abilities' => ['delivery'],
            'delivery_person' => new DeliveryPersonResource($person),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var DeliveryPerson $person */
        $person = $request->user();

        return ApiResponse::success(new DeliveryPersonResource($person));
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();
        $token?->delete();

        return ApiResponse::success(['logged_out' => true]);
    }
}
