<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Tests\TestCase;

class ApiProductFilter extends TestCase
{
    use RefreshDatabase;

    public function test_getProductsByName()
    {
        Product::factory()->create([
            'name' => 'Cucumber'
        ]);

        $this->getJson('/api/v1/products?' . Arr::query([
            'name' => 'umbe',
        ]))
            ->assertStatus(200)
            ->assertJsonCount(1);

        $this->getJson('/api/v1/products?' . Arr::query([
            'name' => 'fffff',
        ]))
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function test_getProductsByPriceFrom()
    {
        Product::factory()->create([
            'price' => 90
        ]);

        $this->getJson('/api/v1/products?' . Arr::query([
            'price_from' => 90,
        ]))
            ->assertStatus(200)
            ->assertJsonCount(1);

        $this->getJson('/api/v1/products?' . Arr::query([
            'price_from' => 100,
        ]))
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function test_getProductsByPriceTo()
    {
        Product::factory()->create([
            'price' => 90
        ]);

        $this->getJson('/api/v1/products?' . Arr::query([
            'price_to' => 100,
        ]))
            ->assertStatus(200)
            ->assertJsonCount(1);

        $this->getJson('/api/v1/products?' . Arr::query([
            'price_to' => 50,
        ]))
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function test_getProductsByPriceFromTo()
    {
        Product::factory()->create([
            'price' => 90
        ]);

        $this->getJson('/api/v1/products?' . Arr::query([
            'price_from' => 50,
            'price_to' => 150,
        ]))
            ->assertStatus(200)
            ->assertJsonCount(1);

        $this->getJson('/api/v1/products?' . Arr::query([
            'price_from' => 50,
            'price_to' => 80,
        ]))
            ->assertStatus(200)
            ->assertJsonCount(0);

        $this->getJson('/api/v1/products?' . Arr::query([
            'price_from' => 100,
            'price_to' => 800,
        ]))
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function test_getProductsByPublished()
    {
        Product::factory()->create([
            'published' => false
        ]);

        $this->getJson('/api/v1/products?' . Arr::query([
            'published' => false,
        ]))
            ->assertStatus(200)
            ->assertJsonCount(1);

        $this->getJson('/api/v1/products?' . Arr::query([
            'published' => true,
        ]))
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function test_getProductsByDeleted()
    {
        $product = Product::factory()->create();
        $product->delete();

        $this->getJson('/api/v1/products?' . Arr::query([
            'deleted' => true,
        ]))
            ->assertStatus(200)
            ->assertJsonCount(1);

        $this->getJson('/api/v1/products?' . Arr::query([
            'deleted' => false,
        ]))
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function test_getProductsByIdCategories()
    {
        $product = Product::factory()->hasCategories(5)->create();
        $categories = $product->categories;

        $this->getJson('/api/v1/products?' . Arr::query([
            'id_categories' => [$categories[0]->id, $categories[1]->id, $categories[2]->id],
        ]))
            ->assertStatus(200)
            ->assertJsonCount(1);

        $this->getJson('/api/v1/products?' . Arr::query([
            'id_categories' => [9000, 9500],
        ]))
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function test_getProductsByNameCategory()
    {
        Product::factory()->hasCategories(1, [
            'name' => 'Car'
        ])->create();
        Product::factory()->hasCategories(1, [
            'name' => 'Fruits'
        ])->create();

        $this->getJson('/api/v1/products?' . Arr::query([
            'name_category' => 'ca',
        ]))
            ->assertStatus(200)
            ->assertJsonCount(1);

        $this->getJson('/api/v1/products?' . Arr::query([
            'name_category' => 'r',
        ]))
            ->assertStatus(200)
            ->assertJsonCount(2);

        $this->getJson('/api/v1/products?' . Arr::query([
            'name_category' => 'den',
        ]))
            ->assertStatus(200)
            ->assertJsonCount(0);
    }
}
