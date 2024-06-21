<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Person;
use App\Models\Contact;

class ClientTest extends TestCase
{

    public function test_list_clients()
    {
        $response = $this->getJson('/api/clients');

        $response->assertOk();
    }

    public function test_create_client()
    {
        $client = $this->create_client(true);

        $response = $this->postJson('/api/clients', $client);
        
        $response->assertCreated();
    }

    public function test_update_client()
    {
        $client = $this->create_client(false);
        $id = $this->last_id();
    
        $response = $this->putJson("/api/clients/{$id}", $client);
    
        $response->assertOk();
    }

    public function test_delete_client()
    {
        $id = $this->last_id();
    
        $response = $this->deleteJson("/api/clients/{$id}");
    
        $response->assertOk();
    }
    
    private function last_id()
    {   
        $lastPerson = Person::max('id');
        return $lastPerson;
    }

    private function create_client($createOrUpdate)
    {
        // Datos comunes
        $data = [
            'person' => [
                'firstName' => "Luigi",
                'secondName' => "Esteban",
                'firstLastName' => "Moto",
                'secondLastName' => "Solís",
                'gender' => true,
                'dateBirth' => "2020-6-17",
            ],
            'contacts' => [
                [
                    'value' => "luigi@example.com",
                    'type_contact_id' => 1
                ],
                [
                    'value' => "86452564",
                    'type_contact_id' => 2
                ]
            ],
            'direction' => [
                'description' => "Calle Principal 123",
                'district_id' => 20201
            ]
        ];

        if (!$createOrUpdate) {
            $data['person']['firstName'] = "Mario"; 
            $data['contacts'][0]['value'] = "mario@example.com";
        }

        return $data;
    }
    
}
