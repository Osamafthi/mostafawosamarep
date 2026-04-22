<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Category\StoreCategoryRequest;
use App\Http\Requests\V1\Category\UpdateCategoryRequest;
use App\Http\Resources\V1\CategoryResource;
use App\Models\Category;
use App\Support\ApiResponse;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::query()->orderBy('name')->get();

        return ApiResponse::success(
            CategoryResource::collection($categories)->resolve()
        );
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());

        return ApiResponse::created((new CategoryResource($category))->resolve());
    }

    public function update(UpdateCategoryRequest $request, int $id): JsonResponse
    {
        $category = Category::query()->find($id);

        if (! $category) {
            return ApiResponse::notFound('Category not found');
        }

        $category->fill($request->validated());
        $category->save();

        return ApiResponse::success((new CategoryResource($category))->resolve());
    }

    public function destroy(int $id): JsonResponse
    {
        $category = Category::query()->find($id);

        if (! $category) {
            return ApiResponse::notFound('Category not found');
        }

        try {
            $category->delete();
        } catch (QueryException $e) {
            return ApiResponse::error(
                'Category cannot be deleted because it is referenced by existing products.',
                409
            );
        }

        return ApiResponse::success(['deleted' => true]);
    }
}
