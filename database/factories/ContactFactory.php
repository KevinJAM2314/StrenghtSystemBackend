<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition($personId = null): array
    {
        return [
            'value' => fake()->unique()->safeEmail(),
            'type_contact_id' => 1,
            'person_id' => $personId,
        ];
    }
}
