<?php


use App\Models\Product;

beforeEach(function () {
    $this->user = createUser();
    $this->admin = createUser(true);
});

test('home page contains empty table', function () {

    $this->actingAs($this->user)
        ->get('/products')
        ->assertStatus(200)
        ->assertDontSee('No products found');
});

test('home page contains non-empty table', function () {
    $product = Product::create([
        'name' => 'Product 1',
        'price' => 100
    ]);

    $this->actingAs($this->user)
        ->get('/products')
        ->assertStatus(200)
        ->assertDontSee('No products found')
        ->assertSee('Product 1')
        ->assertViewHas('products', function ($products) use ($product) {
            return $products->contains($product);
        });
});


test('product store successful', function ()
{
    $product = [
        'name' => 'Product 1',
        'price' => 100
    ];

    $this->actingAs($this->admin)
        ->post('/product/store', $product)
        ->assertRedirect('/products');

    $this->assertDatabaseHas('product', $product);

    $lastProduct = Product::latest()->first();

    expect($product['name'])->toBe($lastProduct->name);
    expect($product['price'])->toBe($lastProduct->price);


});



