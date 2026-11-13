<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucfirst(fake()->words(3, true));

        return [
            'store_id' => Store::factory(),
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 9999),
            'description' => fake()->sentence(12),
            'sku' => strtoupper(fake()->bothify('SKU-###??')),
            'base_price' => fake()->randomFloat(2, 5, 120),
            'image_url' => null,
            'badge' => null,
            'prep_note' => 'Listo en minutos',
            'is_active' => true,
        ];
    }
}
