<?php

namespace Database\Factories;

use App\Models\Color;
use App\Models\Shoe;
use App\Models\Size;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventory>
 */
class InventoryFactory extends Factory
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
            'size_id' => Size::factory(),
            'color_id' => Color::factory(),
            'stock' => $this->faker->numberBetween(0, 100),
        ];
    }
}
