<?php

namespace Database\Factories;

use App\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerAddress>
 */
class CustomerAddressFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->randomElement(['Casa', 'Oficina', 'Apartamento']),
            'street_address' => fake()->streetAddress(),
            'reference' => 'Portón azul, timbre 2',
            'latitude' => 10.4880 + fake()->randomFloat(5, -0.02, 0.02),
            'longitude' => -66.8792 + fake()->randomFloat(5, -0.02, 0.02),
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_default' => true,
        ]);
    }
}
