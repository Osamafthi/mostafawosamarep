<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AdminAuthController;
use App\Http\Controllers\Api\V1\Auth\CustomerAuthController;
use App\Http\Controllers\Api\V1\Public\ProductController as PublicProductController;
use App\Http\Controllers\Api\V1\Public\CategoryController as PublicCategoryController;
use App\Http\Controllers\Api\V1\Public\OrderController as PublicOrderController;
use App\Http\Controllers\Api\V1\Customer\ProfileController;
use App\Http\Controllers\Api\V1\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\V1\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\V1\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\V1\Admin\UploadController as AdminUploadController;
use App\Http\Controllers\Api\V1\Admin\AdminController as AdminAccountController;

Route::prefix('v1')->group(function () {
    Route::get('/', function () {
        return response()->json([
            'success' => true,
            'data' => [
                'name' => config('app.name'),
                'version' => 'v1',
            ],
        ]);
    });

    // Public auth
    Route::post('auth/admin/login', [AdminAuthController::class, 'login']);
    Route::post('auth/customer/register', [CustomerAuthController::class, 'register']);
    Route::post('auth/customer/login', [CustomerAuthController::class, 'login']);

    // Public email verification link (signed; clicked from mailbox).
    // Named `verification.verify` because Laravel's built-in VerifyEmail
    // notification builds its signed URL via that route name.
    Route::get('auth/customer/verify-email/{id}/{hash}', [CustomerAuthController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    // Public catalog
    Route::get('products', [PublicProductController::class, 'index']);
    Route::get('products/{id}', [PublicProductController::class, 'show'])->whereNumber('id');
    Route::get('categories', [PublicCategoryController::class, 'index']);

    // Public checkout (guest or authenticated customer)
    Route::post('orders', [PublicOrderController::class, 'store']);

    // Customer (requires a token with the "customer" ability)
    Route::middleware(['auth:sanctum', 'abilities:customer'])
        ->prefix('customer')
        ->group(function () {
            Route::post('logout', [CustomerAuthController::class, 'logout']);
            Route::post('email/verification-notification', [CustomerAuthController::class, 'resendVerification'])
                ->middleware('throttle:6,1');
            Route::get('me', [ProfileController::class, 'me']);
            Route::get('orders', [ProfileController::class, 'orders']);
            Route::get('orders/{id}', [ProfileController::class, 'showOrder'])->whereNumber('id');
        });

    // Admin (requires a token with the "admin" ability)
    Route::middleware(['auth:sanctum', 'abilities:admin'])
        ->prefix('admin')
        ->group(function () {
            Route::post('logout', [AdminAuthController::class, 'logout']);
            Route::get('me', [AdminAuthController::class, 'me']);
            Route::get('stats', [AdminProductController::class, 'stats']);

            Route::get('products', [AdminProductController::class, 'index']);
            Route::post('products', [AdminProductController::class, 'store']);
            Route::get('products/{id}', [AdminProductController::class, 'show'])->whereNumber('id');
            Route::put('products/{id}', [AdminProductController::class, 'update'])->whereNumber('id');
            Route::delete('products/{id}', [AdminProductController::class, 'destroy'])->whereNumber('id');

            Route::get('categories', [AdminCategoryController::class, 'index']);
            Route::post('categories', [AdminCategoryController::class, 'store']);
            Route::put('categories/{id}', [AdminCategoryController::class, 'update'])->whereNumber('id');
            Route::delete('categories/{id}', [AdminCategoryController::class, 'destroy'])->whereNumber('id');

            Route::get('admins', [AdminAccountController::class, 'index']);
            Route::post('admins', [AdminAccountController::class, 'store']);
            Route::delete('admins/{id}', [AdminAccountController::class, 'destroy'])->whereNumber('id');

            Route::get('orders', [AdminOrderController::class, 'index']);
            Route::get('orders/stats', [AdminOrderController::class, 'stats']);
            Route::get('orders/{id}', [AdminOrderController::class, 'show'])->whereNumber('id');
            Route::patch('orders/{id}/status', [AdminOrderController::class, 'updateStatus'])->whereNumber('id');
            Route::patch('orders/{id}/payment-status', [AdminOrderController::class, 'updatePaymentStatus'])->whereNumber('id');

            Route::post('upload', [AdminUploadController::class, 'store']);
        });
});
