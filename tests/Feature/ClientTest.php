<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Person;
use App\Models\Contact;

class ClientTest extends TestCase
{
    use WithFaker; 

    public function test_list_clients()
    {
        $response = $this->getJson('/api/clients');

        $response->assertOk();
    }

    public function test_create_client()
    {
        $client = $this->create_client();

        $response = $this->postJson('/api/clients', $client);
        
        $response->assertCreated();
    }

    public function test_update_client()
    {
        $client = $this->create_client();
        $id = $this->last_id();
    
        $response = $this->putJson("/api/clients/{$id}", $client);
    
        $response->assertOk();
    }
    
    public function test_delete_client()
    {
        $id = $this->last_id();
    
        $response = $this->deleteJson("/api/clients/{$id}");
    
        $response->assertNoContent();
    }
    

    private function last_id()
    {   
        $lastPerson = Person::latest()->value('id');
        return $lastPerson;
    }

    private function create_client()
    {
        $person = Person::factory()->make();
        $contact = Contact::factory()->make();
    
        $data = [
            'person' => $person->getAttributes(),
            'contacts' => [
                $contact->getAttributes()
            ],
            'direction' => [
                'description' => substr($this->faker->text(), 0, 50),
                'district_id' => $this->faker->randomElement([10101, 11911]),
            ]
        ];
        return $data;
    }
}
