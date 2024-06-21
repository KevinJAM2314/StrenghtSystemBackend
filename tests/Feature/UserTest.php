<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use App\Models\Person;
use App\Models\User;

class UserTest extends TestCase
{
    use WithFaker;

    public function test_list_users()
    {
        $response = $this->getJson('/api/users');

        $response->assertOk();
    }

    public function test_register()
    {
        $user = $this->create_user();

        $response = $this->postJson('api/users' , $user);

        $response->assertCreated();
    }

    private function last_id()
    {   
        $lastCategory = User::max('id');
        return $lastCategory;
    }

    private function create_user()
    {
        $user = [
            'person' => [
                "firstName" => "Carlos",
                "secondName" => "Jose",
                "firstLastName" => "Vargas",
                "secondLastName" => "Mora",
                "gender" => true,
            ],
            'user' =>[
                'userName' => $this->faker->userName(),
                'password' => bcrypt('password'),
            ]
        ];
       
        return $user;
    }
}

