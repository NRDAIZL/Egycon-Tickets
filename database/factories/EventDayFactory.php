<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EventDayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'date' => $this->faker->date(),
            'start_time' => $this->faker->time(),
            'end_time' => $this->faker->time(),
        ];
    }
}
