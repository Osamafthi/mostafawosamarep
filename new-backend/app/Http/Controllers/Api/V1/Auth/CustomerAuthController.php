<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Auth\CustomerLoginRequest;
use App\Http\Requests\V1\Auth\CustomerRegisterRequest;
use App\Http\Resources\V1\CustomerResource;
use App\Models\Customer;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerAuthController extends Controller
{
    public function register(CustomerRegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $customer = Customer::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
        ]);

        $token = $customer->createToken('customer-api', ['customer'])->plainTextToken;

        return ApiResponse::created([
            'token' => $token,
            'token_type' => 'Bearer',
            'abilities' => ['customer'],
            'customer' => new CustomerResource($customer),
        ]);
    }

    public function login(CustomerLoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $customer = Customer::query()->where('email', $credentials['email'])->first();

        if (! $customer || ! Hash::check($credentials['password'], $customer->password)) {
            return ApiResponse::unauthorized('Invalid credentials');
        }

        $token = $customer->createToken('customer-api', ['customer'])->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'token_type' => 'Bearer',
            'abilities' => ['customer'],
            'customer' => new CustomerResource($customer),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();
        $token?->delete();

        return ApiResponse::success(['logged_out' => true]);
    }
}
