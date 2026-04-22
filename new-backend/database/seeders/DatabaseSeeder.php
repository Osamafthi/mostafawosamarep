<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Admin::firstOrCreate(
            ['email' => 'admin@ecommerce.local'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin123'),
            ]
        );

        $categories = [
            ['name' => 'Electronics',       'slug' => 'electronics',        'description' => 'Phones, TVs, audio and accessories.'],
            ['name' => 'Fashion',           'slug' => 'fashion',            'description' => "Men's, women's and kids' apparel."],
            ['name' => 'Home & Office',     'slug' => 'home-and-office',    'description' => 'Furniture, decor and office supplies.'],
            ['name' => 'Health & Beauty',   'slug' => 'health-and-beauty',  'description' => 'Personal care, cosmetics and wellness.'],
            ['name' => 'Computing',         'slug' => 'computing',          'description' => 'Laptops, desktops, peripherals.'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['slug' => $cat['slug']],
                [
                    'name' => $cat['name'],
                    'description' => $cat['description'],
                ]
            );
        }
    }
}
