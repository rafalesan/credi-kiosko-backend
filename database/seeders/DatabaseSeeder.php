<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Credit;
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
            ->has(
                Credit::factory($faker->numberBetween(6, 13))
                    ->hasAttached(Product::all()->random($faker->numberBetween(1, 3)),
                                  ['product_name' => '',
                                   'price' => '0',
                                   'quantity' => '0',
                                   'total' => '0'])
            )
            ->create()
            ->each(function (Customer $customer) use ($faker) {
                $credits = $customer->credits;
                foreach($credits as $credit) {
                    $total = '0';
                    foreach($credit->products as $product) {
                        $pivot = $product->pivot;
                        $pivot->product_name = $product->name;
                        $pivot->price = $product->price;
                        $pivot->quantity = $faker->numberBetween(1, 2);
                        $pivot->total = $product->price * $pivot->quantity;
                        $credit->products()->updateExistingPivot($product->id, $pivot->attributesToArray());
                        $total += $pivot->total;
                    }
                    $credit->total = $total;
                    $credit->update();
                }
            });

    }
}
