<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Auth\AdminLoginRequest;
use App\Http\Resources\V1\AdminResource;
use App\Models\Admin;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function login(AdminLoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $admin = Admin::query()->where('email', $credentials['email'])->first();

        if (! $admin || ! Hash::check($credentials['password'], $admin->password)) {
            return ApiResponse::unauthorized('Invalid credentials');
        }

        $token = $admin->createToken('admin-api', ['admin'])->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'token_type' => 'Bearer',
            'abilities' => ['admin'],
            'admin' => new AdminResource($admin),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var Admin $admin */
        $admin = $request->user();

        return ApiResponse::success(new AdminResource($admin));
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();
        $token?->delete();

        return ApiResponse::success(['logged_out' => true]);
    }
}
