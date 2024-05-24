<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Direction>
 */
class DirectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition($personId = null): array
    {
        return [
            'description' => $this->faker->address(),
            'geo_id' => $this->faker->randomElement([10101, 11911]),
            'person_id' => $personId,
        ];
    }
}
