<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Sale;
use App\Models\Person;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\UploadedFile;
use App\Models\InventoryXProduct;

class SaleTest extends TestCase
{
    use WithFaker;

    public function test_list_sales()
    {
        $response = $this->get('/api/sales');

        $response->assertOk();
    }

    public function test_create_sale()
    {
        $sale = $this->create_sale(true);

        $response = $this->postJson('/api/sales', $sale);
        
        $response->assertCreated();
    }

    private function create_sale($createOrUpdate)
    {
        $client = null;
        $category = null;
        $product = null;
        $InventaryxProduct = null;

        if($createOrUpdate){

            $client = Person::create([
                'firstName' => "Luigi",
                'secondName' => "Esteban",
                'firstLastName' => "Moto",
                'secondLastName' => "Solís",
                'gender' => true,
                'dateBirth' => "2020-6-17",
                'type_person_id' => 2,
            ]);

            $category = Category::create([
                'name' => 'Ropa y calzado deportivo',
                'duration' => null, // Si 'duration' puede ser NULL en tu base de datos, así está bien
            ]);

            $product =  Product::create([
                'name' => 'Ropa y calzado',
                'description' => 'Tenis Adidas',
                'price' => 25000,
                'category_id' => $category->id,
                'image' => UploadedFile::fake()->image('test.png'), 
                'quantity' => 20,
                'available' => true
            ]);

            $InventaryxProduct = InventoryXProduct::create([
                'quantity' => 20,
                'available' => true,
                'product_id' => $product->id,
                'inventory_id' => 1
            ]);
        }

        $data = [
            'person_id' =>  $client->id,
            'sale_details' => [
                [
                    'quantity' => 2,
                    'product_id' => $product->id,
                ]
            ]
        ];

        return $data;
    }
}
