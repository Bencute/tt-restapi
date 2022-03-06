<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiProduct extends TestCase
{
    use RefreshDatabase;

    public function test_createProductError()
    {
        $this->postJson('/api/v1/products', [])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors',
            ]);

        $this->postJson('/api/v1/products', [
                'name' => 'Burger',
                'price' => 19.99,
                'published' => true,
                'categories' => [12,5],
            ])
            ->assertStatus(422);

        $this->postJson('/api/v1/products', [
                'name' => 'Burger',
                'price' => 19.99,
                'published' => true,
                'categories' => [Category::factory()->create()->id],
            ])
            ->assertStatus(422);

        $this->postJson('/api/v1/products', [
                'name' => 'Burger',
                'price' => 19.99,
                'published' => true,
                'categories' => Category::factory()->count(12)->create()->pluck('id'),
            ])
            ->assertStatus(422);
    }

    public function test_createProductSuccess()
    {
        $category = Category::factory()->count(2)->create();
        $response = $this->postJson('/api/v1/products', [
            'name' => 'Burger',
            'price' => 19.99,
            'published' => true,
            'categories' => $category->pluck('id'),
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
            ]);
    }

    public function test_showProducts()
    {
        Product::factory()
            ->count(5)
            ->create()
            ->each(function ($item) {
                /** @var Product $item */
                $item->categories()->sync(Category::factory()->count(rand(2,10))->create());
            });

        $response = $this->getJson('/api/v1/products');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'price',
                    'published',
                ]
            ]);
    }

    public function test_updateProduct()
    {
        /** @var Product $product */
        $product = Product::factory()->create();

        $response = $this->putJson("/api/v1/products/{$product->id}", ['name' => 'Car']);

        $response
            ->assertStatus(200)
            ->assertJson([
                'id' => $product->id,
                'name' => 'Car',
            ]);
    }

    public function test_getProduct()
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
            ]);
    }

    public function test_deleteProduct()
    {
        /** @var Product $product */
        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/v1/products/{$product->id}");

        $response
            ->assertStatus(204);

        $this->assertNotNull(Product::withTrashed()->find($product->id)->{$product->getDeletedAtColumn()});
    }
}
