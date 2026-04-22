<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    /**
     * Default number of products to create when running this seeder.
     * Override by passing `--class=ProductsSeeder` along with the
     * PRODUCTS_SEED_COUNT env variable, e.g.:
     *   PRODUCTS_SEED_COUNT=500 php artisan db:seed --class=ProductsSeeder
     */
    private const DEFAULT_COUNT = 300;

    /**
     * Existing image URLs to randomly assign to products.
     * These are real files already in assets/js/uploads/.
     * Must include the base path /mostafawosama for XAMPP compatibility.
     */
    private const EXISTING_IMAGES = [
        '/mostafawosama/assets/js/uploads/20260419_030351_86010ab0a706.webp',
        '/mostafawosama/assets/js/uploads/20260419_031154_292d217105d6.webp',
        '/mostafawosama/assets/js/uploads/20260419_034001_d7e91e40fc5b.jpg',
        '/mostafawosama/assets/js/uploads/20260420_033224_cdf4a6c51f10.jpeg',
    ];

    public function run(): void
    {
        $count = (int) (env('PRODUCTS_SEED_COUNT') ?: self::DEFAULT_COUNT);
        $count = max(1, $count);

        if (Category::query()->count() === 0) {
            $this->command?->warn('No categories exist. Run `php artisan db:seed` first (or seed categories) before seeding products.');
            return;
        }

        $this->command?->info("Seeding {$count} products with existing images…");

        $chunkSize = 50;
        $remaining = $count;
        $created = 0;

        while ($remaining > 0) {
            $batch = min($chunkSize, $remaining);

            // Create batch with factory, then assign random existing images
            $products = Product::factory()->count($batch)->make();

            foreach ($products as $product) {
                $product->image_url = $this->randomExistingImage();
                $product->save();
                $created++;
            }

            $remaining -= $batch;
            $this->command?->getOutput()?->write('.');
        }

        $this->command?->getOutput()?->writeln('');
        $this->command?->info("Done. Created {$created} products.");
    }

    private function randomExistingImage(): string
    {
        return self::EXISTING_IMAGES[array_rand(self::EXISTING_IMAGES)];
    }
}
