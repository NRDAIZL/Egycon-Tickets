<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

class EventFactory extends Factory
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
            'description' => $this->faker->text,
            'location' => $this->faker->address,
            'logo' => $this->faker->imageUrl(),
            'banner' => $this->faker->imageUrl(),
            'google_maps_url' => $this->faker->url,
            'registration_start' => Date::now(),
            'registration_end' => Date::now()->addDays(1),
            'slug' => $this->faker->slug,
        ];
    }

    public function withTicketTypes($count = 5)
    {
        return $this->afterCreating(function (\App\Models\Event $event) use ($count){
            $event->ticket_types()->saveMany(\App\Models\TicketType::factory()->count($count)->make());
        });
    }

    public function withPaymentMethods()
    {
        return $this->afterCreating(function (\App\Models\Event $event){
            $payment_metods = [
                ['name' => 'Vodafone Cash', 'payment_method_id' => 1, 'account_name' => $this->faker->name, 'account_number' => $this->faker->phoneNumber],
                ['name' => 'InstaPay', 'payment_method_id' => 2, 'account_name' => $this->faker->name, 'account_number' => $this->faker->phoneNumber],
                ['name' => 'Credit Card', 'payment_method_id' => 3, 'account_name' => $this->faker->name, 'account_number' => $this->faker->randomNumber(8)],
            ];

            foreach($payment_metods as $payment_method){
                $event->payment_methods()->save(\App\Models\EventPaymentMethod::factory()->make($payment_method));
            }
        });
    }

    public function withEventDays($days = 1){
        return $this->afterCreating(function (\App\Models\Event $event) use ($days){
            $event_days = [
                ['date' => Date::now(), 'start_time' => '08:00', 'end_time' => '17:00'],
            ];

            if($days > 1){
                for($i = 1; $i < $days; $i++){
                    $event_days[] = ['date' => Date::now()->addDays($i), 'start_time' => '08:00', 'end_time' => '17:00'];
                }
            }

            foreach($event_days as $event_day){
                $event->event_days()->save(\App\Models\EventDay::factory()->make($event_day));
            }
        });
    }

}
