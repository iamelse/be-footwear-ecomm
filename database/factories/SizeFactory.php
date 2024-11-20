<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Size>
 */
class SizeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'size_us' => $this->faker->randomFloat(1, 5, 15),
            'size_eu' => $this->faker->randomFloat(1, 35, 50),
            'size_uk' => $this->faker->randomFloat(1, 7, 10)
        ];
    }
}
