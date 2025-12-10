<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Faker\Factory as FakerStaticFactory;  // Alias Faker\Factory to avoid naming conflict

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * The current Faker instance.
     *
     * @var \Faker\Generator
     */
    protected $faker; // Removed 'Generator' type hint as per error message

    /**
     * Create a new factory instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->faker = FakerStaticFactory::create(); // Use the aliased Faker factory
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->words(2, true) . ' ' . $this->faker->unique()->numberBetween(100, 999),
            'description' => $this->faker->sentence(),
            'category_id' => Category::inRandomOrder()->first()->id ?? Category::factory(),
            'unit_id' => Unit::inRandomOrder()->first()->id ?? Unit::factory(),
            'stock_quantity' => $this->faker->numberBetween(10, 200),
            'cost_price' => $this->faker->randomFloat(2, 5, 50), // Cost price between 5 and 50
            'selling_price' => $this->faker->randomFloat(2, 51, 100), // Selling price between 51 and 100
            // 'image_url' => $this->faker->imageUrl(640, 480, 'products', true),
        ];
    }
}
