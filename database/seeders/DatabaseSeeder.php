<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        Business::factory(1)->create();
        User::factory(1)->create([
            'name' => 'Rafael Alegría Sánchez',
            'nickname' => 'rafalesan',
            'email' => 'rafalesan96@gmail.com',
        ]);
        Product::factory(8)->create();
        Customer::factory(5)
            ->hasAttached(Business::first(), ['business_customer_name' => $faker->unique()->name(),
                                              'business_customer_nickname' => $faker->unique()->userName()])
            ->create();

    }
}
