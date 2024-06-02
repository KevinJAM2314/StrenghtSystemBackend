<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Str;
use App\Models\Person;

class UserTest extends TestCase
{
  use WithFaker;
  
    public function test_register()
    {
        $user = $this->create_user();

        $response = $this->postJson('api/users' , $user);

        $response->assertCreated();
    }

    private function create_user()
    {
        $person = Person::factory()->make();

        $user = [
            'person' => $person->getAttributes(),
            'user' =>[
                'userName' => $this->faker->userName(),
                'confirmated' => 0,
                'password' => bcrypt('password'),
                'remember_token' => Str::random(10),
            ]
        ];
       
        return $user;
    }
}

