<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Credit>
 */
class CreditFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {

        $user = $this->faker->randomElement(User::all());

        return [
            'business_id' => $user->business_id,
            'user_id' => $user->id,
            'customer_id' => $this->faker->randomElement(Customer::all(['id'])),
            'date' => $this->faker->dateTimeBetween("-1 months")->format('Y-m-d H:i:s'),
            'total' => '0'
        ];
    }
}
