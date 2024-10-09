<?php

namespace Tests\Feature;

use App\Models\Categories;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_access_category_page()
    {
        $response = $this->get('/categories');

        $response->assertRedirect('/login');
    }

    public function test_home_page_contains_empty_table()
    {
        $user= User::factory()->create();
        $response = $this->actingAs($user)->get('/categories');

        $response->assertStatus(200);
        $response->assertSee('No categories found');
    }

    public function test_home_page_contains_non_empty_table()
    {
        $category = Category::factory()->create();
        $user= User::factory()->create();

        $response = $this->actingAs($user)->get('/categories');
        $response->assertStatus(200);
        $response->assertDontSee('No categories found');
        $response->assertSee($category->name);

    }
}
