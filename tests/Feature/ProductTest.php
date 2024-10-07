<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;
    private $user, $admin;
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser();
        $this->admin = $this->createUser(true);
    }

    public function test_home_page_contains_empty_table()
    {
        $response = $this->actingAs($this->user)->get('/products');

        $response->assertStatus(200);
        $response->assertDontSee('No products found');
    }

    public function test_homepage_contains_non_empty_table()
    {
        $product = Product::create([
            'name' => 'Product 1',
            'price' => 100
        ]);

        $response = $this->actingAs($this->user)->get('/products');

        $response->assertStatus(200);
        $response->assertDontSee('No products found');
        $response->assertSee('Product 1');
        $response->assertViewHas('products', function ($products) use ($product) {
            return $products->contains($product);
        });
    }

    public function test_paginated_products_table_doesnt_contains_11th_records()
    {
        $products = Product::factory()->count(11)->create();
        $lastProduct = $products->last();

        $response = $this->actingAs($this->user)->get('/products');

        $response->assertStatus(200);
        $response->assertViewHas('products', function ($collection) use ($lastProduct) {
            return !$collection->contains($lastProduct);
        });
    }

    public function test_admin_can_see_product_create_button()
    {
        $response = $this->actingAs($this->admin)->get('/products');

        $response->assertStatus(200);
        $response->assertSee('Create Product');
    }

    public function test_non_admin_cannot_see_product_create_button()
    {
        $response = $this->actingAs($this->user)->get('/products');

        $response->assertStatus(200);
        $response->assertDontSee('Create Product');
    }

    public function test_admin_can_access_product_create_page()
    {
        $response = $this->actingAs($this->admin)->get('/product/create');

        $response->assertStatus(200);

    }

    public function test_non_admin_cannot_access_product_create_page()
    {
        $response = $this->actingAs($this->user)->get('/product/create');

        $response->assertStatus(403);
    }

    public function test_product_store_successfully()
    {
        $product = [
            'name' => 'Product 1',
            'price' => 100
        ];

        $response = $this->actingAs($this->admin)->post('/product/store', $product);

        $response->assertStatus(302);
        $response->assertRedirect('/products');
        $this->assertDatabaseHas('product', $product);

        $lastProduct = Product::latest()->first();
        $this->assertEquals($product['name'], $lastProduct->name);
        $this->assertEquals($product['price'], $lastProduct->price);
    }

    public function test_edit_form_contains_product()
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin)->get('/product/' . $product->id . '/edit');

        $response->assertStatus(200);
        $response->assertSee('value="'. $product->name . '"', false);
        $response->assertSee('value="'. $product->price . '"', false);
        $response->assertViewHas('product', $product);
    }

    public function test_product_update_successfully()
    {
        $product = Product::factory()->create();
        $newProduct = [
            'name' => 'Product 2',
            'price' => 200
        ];

        $response = $this->actingAs($this->admin)->put('/product/' . $product->id, $newProduct);
        $response->assertStatus(302);
        $response->assertRedirect('/products');
        $this->assertDatabaseHas('product', $newProduct);
        $this->assertDatabaseMissing('product', $product->toArray());
    }

    public function test_product_update_validation_error_redirects_back_to_form()
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin)->put('/product/' . $product->id, [
            'name' => '',
            'price' => ''
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name', 'price']);
        $response->assertInvalid('price');
    }

    public function test_product_deleted_successfully()
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin)->delete('product/'.$product->id);

        $response->assertStatus(302);
        $response->assertRedirect('/products');

        $this->assertDatabaseMissing('product', $product->toArray());
        $this->assertDatabaseCount('product', 0);
    }

    private function createUser($isAdmin=false) : User
    {
        return User::factory()->create(['is_admin' => $isAdmin]);
    }

    public function test_api_return_products_list()
    {
        $products = Product::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->getJson('/api/products');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
        $response->assertJson($products->toArray());

    }

    public function test_api_product_store_successful()
    {
        $product = [
            'name' => 'Product 1',
            'price' => 100
        ];

        $response = $this->postJson('/api/products', $product);
        $response->assertStatus(201);
        $response->assertJson($product);
    }



    public function test_api_product_invalid_store_return_error()
    {
        $product = [
            'name' => '',
            'price' => 100
        ];

        $response = $this->postJson('/api/products', $product);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');

    }

}
