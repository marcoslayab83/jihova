<?php

namespace Database\Factories;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\StoreBranch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryMovement>
 */
class InventoryMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'branch_id' => StoreBranch::factory(),
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'type' => 'entry',
            'quantity' => 5,
            'stock_before' => 0,
            'stock_after' => 5,
            'reason' => fake()->sentence(4),
        ];
    }
}
