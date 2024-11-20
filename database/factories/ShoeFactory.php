<?php

namespace Database\Factories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shoe>
 */
class ShoeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'brand_id' => Brand::factory(),
            'name' => $this->faker->unique()->word,
            'slug' => Str::slug($name) . '-' . Str::random(5),
            'price' => $this->faker->randomFloat(2, 50, 300),
            'description' => $this->faker->paragraph,
        ];
    }
}
