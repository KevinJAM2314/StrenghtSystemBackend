<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Category;

class CategoryTest extends TestCase
{
    
    public function test_list_categories()
    {
        $response = $this->getJson('/api/categories');

        $response->assertOk();
    }

    public function test_create_category()
    {
        $category = $this->create_category();

        $response = $this->postJson('/api/categories', $category);
        
        $response->assertCreated();
    }

    public function test_update_category()
    {
        $category = $this->create_category();
        $id = $this->last_id();
    
        $response = $this->putJson("/api/categories/{$id}", $category);
    
        $response->assertOk();
    }
    
    public function test_delete_category()
    {
        $id = $this->last_id();
    
        $response = $this->deleteJson("/api/categories/{$id}");
    
        $response->assertNoContent();
    }

    private function last_id()
    {   
        $lastCategory = Category::value('id');
        return $lastCategory;
    }

    private function create_category()
    {
        $category = Category::factory()->make()->getAttributes();
        return $category;
    }
}