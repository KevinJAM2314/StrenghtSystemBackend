<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => substr($this->faker->word(), 0, 20),
            'description' => substr($this->faker->sentence(), 0, 100),
            'image' => 'test/example.png', 
            'price' => $this->faker->randomFloat(2, 1, 9999),
        ];
    }
}
