<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\StoreBranch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StoreBranch>
 */
class StoreBranchFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'branch_name' => 'Sucursal '.fake()->citySuffix(),
            'address' => fake()->streetAddress(),
            'latitude' => 10.4910 + fake()->randomFloat(5, -0.015, 0.015),
            'longitude' => -66.8750 + fake()->randomFloat(5, -0.015, 0.015),
            'delivery_radius_km' => 6.00,
            'base_delivery_fee' => 2.50,
            'avg_prep_time_minutes' => 15,
            'is_open' => true,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_open' => false,
        ]);
    }
}
