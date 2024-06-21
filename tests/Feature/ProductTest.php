<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\UploadedFile;
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
        $product = $this->create_product(true);
        
        $response = $this->postJson('/api/products', $product);
        
        $response->assertCreated();
    }

    public function test_update_product()
    {
        $product = $this->create_product(false);
        $id = $this->last_id();

        $response = $this->postJson("/api/products/{$id}", $product);
    
        $response->assertOk();
    }
    
   public function test_delete_product()
    {
        $id = $this->last_id();
    
        $response = $this->deleteJson("/api/products/{$id}");
    
        $response->assertOk();
    }

    private function last_id()
    {   
        $last_Product = Product::max('id');
        return $last_Product;
    }

    private function last_category_id()
    {   
        $last_Category = Category::max('id');
        return $last_Category;
    }

    private function create_product($createOrUpdate)
    {
        $data = [];

        if($createOrUpdate) {
            $category = Category::create([
                'name' => 'Ropa y calzado deportivo',
                'duration' => null, // Si 'duration' puede ser NULL en tu base de datos, así está bien
            ]);

            $data =
            [
                'name' => 'Proteina',
                'description' => 'Proteina FullTech 200 MG',
                'price' => 30000,
                'category_id' => $category->id,
                'image' => UploadedFile::fake()->image('test.png'), 
                'quantity' => 20,
                'available' => true
            ];
        }

        if (!$createOrUpdate) {
            $categoryActual = $this->last_category_id();

            $categoryUpdate = Category::create([
                'name' => 'Suplementos',
                'duration' => null, // Si 'duration' puede ser NULL en tu base de datos, así está bien
            ]);

            $data =
            [
                'name' => 'Proteina',
                'description' => 'Proteina FullTech 200 MG',
                'price' => 30000,
                'category_id_new' => $categoryActual,
                'category_id_old' => $categoryUpdate->id,
                'quantity' => 20,
                'available' => true
            ];
        }

        return $data;
    }

}
