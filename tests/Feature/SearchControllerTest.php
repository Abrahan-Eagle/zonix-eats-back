<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Commerce;
use App\Models\Product;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SearchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_restaurants_uses_canonical_fields_and_returns_results(): void
    {
        $user = User::factory()->create(['role' => 'users']);
        Profile::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $owner = Profile::factory()->create();
        $commerce = Commerce::factory()->create([
            'profile_id' => $owner->id,
            'business_name' => 'Pizza Valencia',
            'open' => true,
            'status' => 'approved',
        ]);
        Product::factory()->create([
            'commerce_id' => $commerce->id,
            'name' => 'Pizza Especial',
            'available' => true,
        ]);

        $response = $this->getJson('/api/buyer/search/restaurants?search=Pizza');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'items',
                    'restaurants',
                    'data',
                    'pagination',
                    'filters_applied',
                ],
            ]);
    }

    public function test_search_products_rejects_invalid_sort_fields(): void
    {
        $user = User::factory()->create(['role' => 'users']);
        Profile::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $owner = Profile::factory()->create();
        $commerce = Commerce::factory()->create([
            'profile_id' => $owner->id,
            'business_name' => 'Comercio Demo',
            'open' => true,
            'status' => 'approved',
        ]);
        Product::factory()->create([
            'commerce_id' => $commerce->id,
            'name' => 'Hamburguesa',
            'available' => true,
        ]);

        $response = $this->getJson('/api/buyer/search/products?sort_by=__bad__&sort_order=DROP');

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_search_products_includes_canonical_commerce_id_field(): void
    {
        $user = User::factory()->create(['role' => 'users']);
        Profile::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $owner = Profile::factory()->create();
        $commerce = Commerce::factory()->create([
            'profile_id' => $owner->id,
            'business_name' => 'Comercio Canonico',
            'open' => true,
            'status' => 'approved',
        ]);

        Product::factory()->create([
            'commerce_id' => $commerce->id,
            'name' => 'Producto Canonico',
            'available' => true,
        ]);

        $response = $this->getJson('/api/buyer/search/products?commerce_id='.$commerce->id);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.items.0.commerce_id', $commerce->id)
            ->assertJsonPath('data.products.0.commerce_id', $commerce->id)
            ->assertJsonPath('data.data.0.commerce_id', $commerce->id)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'pagination',
                ],
            ]);
    }

    public function test_search_products_includes_category_name_and_image_aliases(): void
    {
        $user = User::factory()->create(['role' => 'users']);
        Profile::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $owner = Profile::factory()->create();
        $commerce = Commerce::factory()->create([
            'profile_id' => $owner->id,
            'business_name' => 'Comercio Contrato',
            'open' => true,
            'status' => 'approved',
        ]);

        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'name' => 'Producto Contrato',
            'available' => true,
            'image' => 'https://cdn.example.com/producto.jpg',
        ]);

        $response = $this->getJson('/api/buyer/search/products?commerce_id='.$commerce->id);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.items.0.id', $product->id)
            ->assertJsonPath('data.products.0.id', $product->id)
            ->assertJsonPath('data.products.0.image', 'https://cdn.example.com/producto.jpg')
            ->assertJsonPath('data.products.0.image_url', 'https://cdn.example.com/producto.jpg')
            ->assertJsonStructure([
                'data' => [
                    'products' => [
                        [
                            'category_name',
                        ],
                    ],
                ],
            ]);
    }

    public function test_buyer_products_applies_category_filter(): void
    {
        $user = User::factory()->create(['role' => 'users']);
        Profile::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $owner = Profile::factory()->create();
        $commerce = Commerce::factory()->create([
            'profile_id' => $owner->id,
            'business_name' => 'Comercio Categoria',
            'open' => true,
            'status' => 'approved',
        ]);

        $categoryA = Category::factory()->create(['name' => 'Categoria A']);
        $categoryB = Category::factory()->create(['name' => 'Categoria B']);

        Product::factory()->create([
            'commerce_id' => $commerce->id,
            'category_id' => $categoryA->id,
            'name' => 'Producto A',
            'available' => true,
        ]);

        Product::factory()->create([
            'commerce_id' => $commerce->id,
            'category_id' => $categoryB->id,
            'name' => 'Producto B',
            'available' => true,
        ]);

        $response = $this->getJson('/api/buyer/products?category_id='.$categoryA->id);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.data.0.category_id', $categoryA->id)
            ->assertJsonCount(1, 'data.data');
    }

    public function test_search_restaurants_defaults_to_open_true(): void
    {
        $user = User::factory()->create(['role' => 'users']);
        Profile::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $owner = Profile::factory()->create();
        Commerce::factory()->create([
            'profile_id' => $owner->id,
            'business_name' => 'Abierto',
            'open' => true,
            'status' => 'approved',
        ]);
        Commerce::factory()->create([
            'profile_id' => $owner->id,
            'business_name' => 'Cerrado',
            'open' => false,
            'status' => 'approved',
        ]);

        $response = $this->getJson('/api/buyer/search/restaurants');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.restaurants');
    }

    public function test_search_products_defaults_to_available_true(): void
    {
        $user = User::factory()->create(['role' => 'users']);
        Profile::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $owner = Profile::factory()->create();
        $commerce = Commerce::factory()->create([
            'profile_id' => $owner->id,
            'open' => true,
            'status' => 'approved',
        ]);

        Product::factory()->create([
            'commerce_id' => $commerce->id,
            'name' => 'Visible',
            'available' => true,
        ]);
        Product::factory()->create([
            'commerce_id' => $commerce->id,
            'name' => 'Oculto',
            'available' => false,
        ]);

        $response = $this->getJson('/api/buyer/search/products?commerce_id='.$commerce->id);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.products');
    }
}
