<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TicketTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'person' => $this->faker->numberBetween(1, 3),
            'type' => $this->faker->randomElement(['qr']),
            'is_active' => true,
            'scan_type' => '',
            'is_disabled' => false,
            'is_visible' => true
        ];
    }
}
