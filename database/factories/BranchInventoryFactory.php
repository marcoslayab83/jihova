<?php

namespace Database\Factories;

use App\Models\BranchInventory;
use App\Models\Product;
use App\Models\StoreBranch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BranchInventory>
 */
class BranchInventoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'branch_id' => StoreBranch::factory(),
            'product_id' => Product::factory(),
            'price' => fake()->randomFloat(2, 6, 95),
            'stock_quantity' => 25,
            'is_available' => true,
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes): array => [
            'stock_quantity' => 0,
            'is_available' => false,
        ]);
    }
}
