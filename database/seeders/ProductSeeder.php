<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Faker\Factory as FakerFactory; // Import Faker Factory
use Faker\Generator;          // Import Faker Generator

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = FakerFactory::create(); // Manually create a Faker instance

        // Get existing Category and Unit IDs
        $categoryIds = Category::pluck('id')->toArray();
        $unitIds = Unit::pluck('id')->toArray();

        // Ensure categories and units exist. If not, create some basic ones.
        if (empty($categoryIds)) {
            $category = Category::create(['name' => 'General Category']);
            $categoryIds[] = $category->id;
        }
        if (empty($unitIds)) {
            $unit = Unit::create(['name' => 'pcs']);
            $unitIds[] = $unit->id;
        }

        // Create 30 products manually
        for ($i = 0; $i < 30; $i++) {
            $costPrice = $faker->randomFloat(2, 5, 50);
            $sellingPrice = $faker->randomFloat(2, $costPrice + 1, $costPrice + 50); // Selling price higher than cost

            Product::create([
                'name' => $faker->words(2, true) . ' ' . $faker->unique()->numberBetween(100, 999),
                'description' => $faker->sentence(),
                'category_id' => $faker->randomElement($categoryIds),
                'unit_id' => $faker->randomElement($unitIds),
                'stock_quantity' => $faker->numberBetween(10, 200),
                'cost_price' => $costPrice,
                'selling_price' => $sellingPrice,
                'image_url' => $faker->imageUrl(640, 480, 'products', true),
            ]);
        }
    }
}