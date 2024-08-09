<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EventPaymentMethodFactory extends Factory
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
            'payment_method_id' => $this->faker->numberBetween(1, 3),
            'account_name' => $this->faker->name,
            'account_number' => $this->faker->phoneNumber,
        ];
    }
}
