<?php

namespace Database\Factories;

use App\Models\Category;
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
    public function definition(): array
    {
        return [
            'code'=>$this->faker->numerify("###########"),
            'name'=>$this->faker->word(),
            'description'=>$this->faker->sentence(),
            'made'=>$this->faker->randomElement(["Johnson","Roche","Pfizer","Bayer","Abbvie"]),
            'measure'=>$this->faker->randomElement(['kg', 'g', 'l', 'ml']),
            'quantity'=>$this->faker->numberBetween(1, 100),
            'price'=>$this->faker->randomFloat(2, 1, 1000),
            'total'=> function (array $attributes) {
                return $attributes['quantity'] * $attributes['price'];
            },
            'category_id'=>Category::all()->random()->id,

        ];
    }
}
