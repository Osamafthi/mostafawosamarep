<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AdminAuthController;
use App\Http\Controllers\Api\V1\Auth\CustomerAuthController;
use App\Http\Controllers\Api\V1\Auth\DeliveryAuthController;
use App\Http\Controllers\Api\V1\Public\ProductController as PublicProductController;
use App\Http\Controllers\Api\V1\Public\CategoryController as PublicCategoryController;
use App\Http\Controllers\Api\V1\Public\OrderController as PublicOrderController;
use App\Http\Controllers\Api\V1\Customer\ProfileController;
use App\Http\Controllers\Api\V1\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\V1\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\V1\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\V1\Admin\UploadController as AdminUploadController;
use App\Http\Controllers\Api\V1\Admin\AdminController as AdminAccountController;
use App\Http\Controllers\Api\V1\Admin\DeliveryPersonController as AdminDeliveryPersonController;
use App\Http\Controllers\Api\V1\Delivery\OrderController as DeliveryOrderController;

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
    Route::post('auth/delivery/login', [DeliveryAuthController::class, 'login']);

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
            Route::patch('orders/{id}/assignee', [AdminOrderController::class, 'assign'])->whereNumber('id');

            // Delivery person CRUD — admin-only.
            Route::get('delivery-persons', [AdminDeliveryPersonController::class, 'index']);
            Route::post('delivery-persons', [AdminDeliveryPersonController::class, 'store']);
            Route::get('delivery-persons/{id}', [AdminDeliveryPersonController::class, 'show'])->whereNumber('id');
            Route::patch('delivery-persons/{id}', [AdminDeliveryPersonController::class, 'update'])->whereNumber('id');
            Route::patch('delivery-persons/{id}/toggle-active', [AdminDeliveryPersonController::class, 'toggleActive'])->whereNumber('id');
            Route::delete('delivery-persons/{id}', [AdminDeliveryPersonController::class, 'destroy'])->whereNumber('id');

            Route::post('upload', [AdminUploadController::class, 'store']);
        });

    // Delivery person (requires a token with the "delivery" ability).
    // Sanctum's CheckAbilities middleware rejects any token that does
    // not carry exactly this ability, so admin or customer tokens get
    // a 403 here even if the URL is hand-typed.
    Route::middleware(['auth:sanctum', 'abilities:delivery'])
        ->prefix('delivery')
        ->group(function () {
            Route::post('logout', [DeliveryAuthController::class, 'logout']);
            Route::get('me', [DeliveryAuthController::class, 'me']);
            Route::get('orders', [DeliveryOrderController::class, 'index']);
            Route::get('orders/{id}', [DeliveryOrderController::class, 'show'])->whereNumber('id');
            Route::patch('orders/{id}/status', [DeliveryOrderController::class, 'updateStatus'])->whereNumber('id');
        });
});
