<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HeroSlideController extends Controller
{
    public function index(): JsonResponse
    {
        $slides = HeroSlide::orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return ApiResponse::success($slides);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'required|string|max:500',
            'link_url' => 'nullable|string|max:500',
            'cta_text' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $validated['is_active'] ?? true;

        $slide = HeroSlide::create($validated);

        return ApiResponse::created($slide);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $slide = HeroSlide::find($id);

        if (! $slide) {
            return ApiResponse::notFound('Hero slide not found');
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:200',
            'subtitle' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string',
            'image_url' => 'sometimes|string|max:500',
            'link_url' => 'sometimes|nullable|string|max:500',
            'cta_text' => 'sometimes|nullable|string|max:50',
            'sort_order' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $slide->update($validated);

        return ApiResponse::success($slide);
    }

    public function destroy(int $id): JsonResponse
    {
        $slide = HeroSlide::find($id);

        if (! $slide) {
            return ApiResponse::notFound('Hero slide not found');
        }

        $slide->delete();

        return ApiResponse::success(['message' => 'Hero slide deleted']);
    }
}
