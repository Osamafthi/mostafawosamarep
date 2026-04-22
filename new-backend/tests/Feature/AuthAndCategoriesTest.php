<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthAndCategoriesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Admin::create([
            'name' => 'Admin',
            'email' => 'admin@ecommerce.local',
            'password' => Hash::make('admin123'),
        ]);

        foreach (['Electronics', 'Fashion', 'Home & Office', 'Health & Beauty', 'Computing'] as $i => $name) {
            Category::create([
                'name' => $name,
                'slug' => 'cat-'.($i + 1),
                'description' => null,
            ]);
        }
    }

    public function test_admin_login_issues_sanctum_token(): void
    {
        $response = $this->postJson('/api/v1/auth/admin/login', [
            'email' => 'admin@ecommerce.local',
            'password' => 'admin123',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonStructure(['data' => ['token', 'admin' => ['id', 'name', 'email']]]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_admin_login_rejects_bad_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/admin/login', [
            'email' => 'admin@ecommerce.local',
            'password' => 'wrong',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error', 'Invalid credentials');
    }

    public function test_public_categories_endpoint_returns_seeded_categories(): void
    {
        $response = $this->getJson('/api/v1/categories');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(5, 'data');
    }

    public function test_admin_categories_endpoint_requires_token_with_admin_ability(): void
    {
        $this->getJson('/api/v1/admin/categories')->assertStatus(401);

        $loginToken = $this->postJson('/api/v1/auth/admin/login', [
            'email' => 'admin@ecommerce.local',
            'password' => 'admin123',
        ])->json('data.token');

        $this->withHeader('Authorization', 'Bearer '.$loginToken)
            ->getJson('/api/v1/admin/categories')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(5, 'data');
    }
}
