<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\StoreAdminRequest;
use App\Http\Resources\V1\AdminResource;
use App\Models\Admin;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(): JsonResponse
    {
        $admins = Admin::query()->orderBy('name')->get();

        return ApiResponse::success(
            AdminResource::collection($admins)->resolve()
        );
    }

    public function store(StoreAdminRequest $request): JsonResponse
    {
        $admin = Admin::create($request->validated());

        return ApiResponse::created((new AdminResource($admin))->resolve());
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $admin = Admin::query()->find($id);

        if (! $admin) {
            return ApiResponse::notFound('Admin not found');
        }

        if ((int) $request->user()->id === $admin->id) {
            return ApiResponse::error('You cannot delete your own admin account.', 422);
        }

        if (Admin::query()->count() <= 1) {
            return ApiResponse::error('At least one admin account must remain.', 422);
        }

        $admin->tokens()->delete();
        $admin->delete();

        return ApiResponse::success(['deleted' => true]);
    }
}
