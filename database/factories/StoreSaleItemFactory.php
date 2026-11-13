<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StoreSale;
use App\Models\StoreSaleItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StoreSaleItem>
 */
class StoreSaleItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $qty = fake()->numberBetween(1, 4);
        $price = fake()->randomFloat(2, 5, 50);

        return [
            'store_sale_id' => StoreSale::factory(),
            'product_id' => Product::factory(),
            'product_name' => ucfirst(fake()->words(3, true)),
            'quantity' => $qty,
            'unit_price' => $price,
            'total_price' => $qty * $price,
        ];
    }
}
