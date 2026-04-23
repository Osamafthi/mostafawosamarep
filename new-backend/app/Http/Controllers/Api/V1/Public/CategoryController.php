<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CategoryResource;
use App\Models\Category;
use App\Support\ApiResponse;
use App\Support\CatalogCache;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $data = CatalogCache::remember(
            'categories',
            'all',
            (int) config('catalog_cache.ttl.categories', 600),
            function () {
                $categories = Category::query()->orderBy('name')->get();

                return CategoryResource::collection($categories)->resolve();
            }
        );

        return ApiResponse::success($data);
    }
}
