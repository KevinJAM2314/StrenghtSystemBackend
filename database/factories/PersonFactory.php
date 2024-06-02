<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Person;
use App\Models\Direction;
use App\Models\Contact;

class PersonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition($typePersonId = null)
    {
        return [
            'firstName' => $this->faker->firstName,
            'secondName' => $this->faker->optional()->firstName,
            'firstLastName' => $this->faker->lastName,
            'secondLastName' => $this->faker->optional()->lastName,
            'gender' => $this->faker->randomElement([0, 1]), 
            'dateBirth' => $this->faker->optional()->date(),
            'type_person_id' => $typePersonId ?? 2 
        ];
    }

    /**
     * After creating the person, assign address and contact.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (Person $person) {
            // Create an associated address for the person
            $person->directions()->save(Direction::factory()->make());
            
            // Create an associated contact for the person
            $person->contacts()->save(Contact::factory()->make());
        });
    }
}
