<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\SyncProductsRequest;
use App\Models\Category;
use App\Models\Product;
use App\Support\ApiResponse;
use App\Support\CatalogCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProductSyncController extends Controller
{
    /**
     * Resolve or create a category by name.
     * Matches case-insensitively on the name column.
     * If name is empty, falls back to "Uncategorized" category.
     */
    private function resolveCategory(?string $categoryName): int
    {
        $name = trim($categoryName ?? '');

        // Fallback to "Uncategorized" if no category name provided
        if ($name === '') {
            $default = Category::firstOrCreate(
                ['name' => 'Uncategorized'],
                ['description' => 'Default category for uncategorized products']
            );
            return $default->id;
        }

        // Try to find existing category by name (case-insensitive)
        $existing = Category::whereRaw('LOWER(name) = LOWER(?)', [$name])->first();

        if ($existing) {
            return $existing->id;
        }

        // Create new category - let the model's booted() hook handle slug generation
        $newCategory = Category::create([
            'name' => $name,
            'description' => null,
        ]);

        return $newCategory->id;
    }

    public function sync(SyncProductsRequest $request): JsonResponse
    {
        $products = $request->validated()['products'];
        $synced = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($products as $item) {
                try {
                    $barcode = $item['barcode'] ?? null;

                    if (!$barcode) {
                        throw new \Exception('Missing barcode');
                    }

                    // Resolve category by name (not by the external category_id)
                    $categoryId = $this->resolveCategory($item['category_name'] ?? null);

                    $attributes = [
                        'name' => trim($item['name'] ?? ''),
                        'price' => $item['price'] ?? 0,
                        'stock' => max(0, (int) round($item['quantity'] ?? 0)),
                        'status' => 'active',
                        'barcode' => $barcode,
                        'category_id' => $categoryId,
                    ];

                    Product::updateOrCreate(
                        ['barcode' => $barcode],
                        $attributes
                    );

                    $synced++;
                } catch (Throwable $e) {
                    $errors[] = [
                        'barcode' => $item['barcode'] ?? null,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();

            return ApiResponse::error('Sync failed', [
                'error' => $e->getMessage(),
            ]);
        }

        DB::table('sync_logs')->insert([
            'total_received' => count($products),
            'total_synced' => $synced,
            'total_errors' => count($errors),
            'source_ip' => $request->ip(),
            'synced_at' => now(),
        ]);

        CatalogCache::flush('products');

        return ApiResponse::success([
            'synced' => $synced,
            'errors' => $errors,
            'total_received' => count($products),
        ]);
    }
}