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
                '*' => [
                    'id',
                    'name',
                ]
            ]);
    }

    public function test_updateCategory()
    {
        /** @var Category $product */
        $category = Category::factory()->create(['name' => 'Fruit']);

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

        $this->deleteJson("/api/v1/categories/{$category->id}")
            ->assertStatus(204);

        $this->assertModelMissing($category);
    }

    public function test_deleteCategoryNotEmpty()
    {
        /** @var Category $category */
        $category = Category::factory()->hasProducts(3)->create();

        $this->deleteJson("/api/v1/categories/{$category->id}")
            ->assertStatus(400);

        $this->assertModelExists($category);
    }
}
