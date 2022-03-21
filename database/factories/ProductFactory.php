<?php

namespace Database\Factories;

use App\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {

        $names = ['Taza de café',
                  'Papita Ziba',
                  'Almuerzo sencillo',
                  'Almuerzo completo',
                  'Lapicero',
                  'Papitas fritas',
                  'Caramelo',
                  'Chicle'];

        $prices = ['7',
                   '10',
                   '70',
                   '100',
                   '8',
                   '20',
                   '2',
                   '2'];

        $nameAndPriceUniqueIndex = $this->faker->unique()->numberBetween(0, 7);

        return [
            'business_id' => $this->faker->randomElement(Business::all(['id'])),
            'name' => $names[$nameAndPriceUniqueIndex],
            'price' => $prices[$nameAndPriceUniqueIndex],
        ];
    }
}
