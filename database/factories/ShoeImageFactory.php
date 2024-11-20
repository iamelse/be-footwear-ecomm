<?php

namespace Database\Factories;

use App\Models\Shoe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ShoeImage>
 */
class ShoeImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shoe_id' => Shoe::factory(),
            'image_url' => $this->faker->imageUrl(400, 400, 'shoes'),
            'is_primary' => $this->faker->boolean(20),
        ];
    }
}
