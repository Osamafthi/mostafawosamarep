<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class HeroSlideController extends Controller
{
    /**
     * Get active hero slides for the storefront.
     */
    public function index(): JsonResponse
    {
        $slides = HeroSlide::active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return ApiResponse::success($slides);
    }
}
