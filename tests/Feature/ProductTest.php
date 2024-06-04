<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\WithFaker;

class ProductTest extends TestCase
{
  use WithFaker;

    public function test_list_products()
    {
        $response = $this->getJson('/api/products');

        $response->assertOk();
    }

    public function test_create_product()
    {
        $product = $this->create_product();
        
        $response = $this->postJson('/api/products', $product);
        
        $response->assertCreated();
    }

    public function test_update_product()
    {
        $product = $this->update_product();
        $id = $this->last_id();

        $response = $this->postJson("/api/products/{$id}", $product);
    
        $response->assertOk();
    }
    
   public function test_delete_product()
    {
        $id = $this->last_id();
    
        $response = $this->deleteJson("/api/products/{$id}");
    
        $response->assertNoContent();
    }

    private function last_id()
    {   
        $lastProduct = Product::max('id');
        return $lastProduct;
    }

    private function create_product()
    {
        $product = Product::factory()->make()->getAttributes();
        return $product;
    }

    private function update_product()
    {
         $category = Category::factory()->create();

         $product = [
            'name' => substr($this->faker->word(), 0, 20),
            'description' => substr($this->faker->sentence(), 0, 100),
            'price' => $this->faker->randomFloat(2, 1, 9999),
            'category_id_new' => $category->id,
            'category_id_old' => $category->id-1
         ];

        return $product;
    }

}
