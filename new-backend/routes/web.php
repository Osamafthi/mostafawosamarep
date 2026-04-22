<?php

use App\Support\ApiResponse;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ApiResponse::success([
        'name' => config('app.name'),
        'api' => url('/api/v1'),
        'health' => url('/up'),
    ]);
});
