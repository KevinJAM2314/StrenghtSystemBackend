<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Person;
use App\Models\Direction;
use App\Models\Contact;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Person>
 */
class PersonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition($typePersonId = null)
    {
       // Crea una nueva persona
        $person = Person::create([
            'firstName' => $this->faker->firstName,
            'secondName' => $this->faker->optional()->firstName,
            'firstLastName' => $this->faker->lastName,
            'secondLastName' => $this->faker->optional()->lastName,
            'gender' => $this->faker->randomElement([0, 1]), 
            'dateBirth' => $this->faker->optional()->date(),
            'type_person_id' => $typePersonId ?? 2 
        ]);

        // Crea una dirección asociada a la persona
        $direction = Direction::factory()->create([
            'person_id' => $person->id,
        ]);

        // Crea un contacto asociado a la persona
        $contact = Contact::factory()->create([
            'person_id' => $person->id,
        ]);

        return [
            'firstName' => $person->firstName,
            'secondName' => $person->secondName,
            'firstLastName' => $person->firstLastName,
            'secondLastName' => $person->secondLastName,
            'gender' => $person->gender,
            'dateBirth' => $person->dateBirth,
            'type_person_id' => $person->type_person_id,
        ];
    }
}
