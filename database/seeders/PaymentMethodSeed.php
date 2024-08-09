<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PaymentMethod::create([
            'name' => 'Vodafone Cash',
            'description' => '',
            'logo' => ''
        ]);
        PaymentMethod::create([
            'name' => 'Kashier',
            'description' => '',
            'logo' => ''
        ]);
        PaymentMethod::create([
            'name' => 'InstaPay',
            'description' => '',
            'logo' => ''
        ]);
    }
}
