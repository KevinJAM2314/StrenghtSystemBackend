<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;
use Illuminate\Http\UploadedFile;

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
        $category = Category::factory()->create();

        return [
            'name' => substr($this->faker->word(), 0, 20),
            'description' => substr($this->faker->sentence(), 0, 100),
            'image' => UploadedFile::fake()->image('test.png'), 
            'price' => $this->faker->randomFloat(2, 1, 9999),
            'category_id' => $category->id
        ];
    }
}
