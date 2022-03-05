<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiCategory extends TestCase
{
    use RefreshDatabase;

    public function test_createCategoryError()
    {
        $response = $this->postJson('/api/v1/categories', []);

        $response
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'name'
                ],
            ]);
    }

    public function test_createCategorySuccess()
    {
        $response = $this->postJson('/api/v1/categories', ['name' => 'Food']);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
            ]);
    }

    public function test_showCategories()
    {
        Category::factory()
            ->count(5)
            ->create();

        $response = $this->getJson('/api/v1/categories');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                    ]
                ]
            ]);
    }

    public function test_updateCategory()
    {
        $category = Category::factory()->create();

        $response = $this->putJson("/api/v1/categories/{$category->id}", ['name' => 'Car']);

        $response
            ->assertStatus(200)
            ->assertJson([
                'id' => $category->id,
                'name' => 'Car',
            ]);
    }

    public function test_getCategory()
    {
        $category = Category::factory()->create();

        $response = $this->getJson("/api/v1/categories/{$category->id}");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
            ]);
    }

    public function test_deleteCategory()
    {
        $category = Category::factory()->create();

        $response = $this->deleteJson("/api/v1/categories/{$category->id}");

        $response
            ->assertStatus(204);

        $this->assertNull(Category::query()->find($category->id));
    }

    public function test_deleteCategoryNotEmpty()
    {
        /** @var Category $category */
        $category = Category::factory()->create();
        $product = Product::factory()->create();
        $category->products()->sync($product);

        $response = $this->deleteJson("/api/v1/categories/{$category->id}");

        $response
            ->assertStatus(400);

        $this->assertNotNull(Category::query()->find($category->id));
    }
}
