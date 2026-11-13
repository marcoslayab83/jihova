<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\StoreBranch;
use App\Models\StoreSale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StoreSale>
 */
class StoreSaleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 10, 150);

        return [
            'store_id' => Store::factory(),
            'branch_id' => fn (array $attrs) => StoreBranch::factory()->create(['store_id' => $attrs['store_id']])->id,
            'user_id' => User::factory(),
            'sale_number' => 'VTA-'.strtoupper(fake()->unique()->bothify('###??-####')),
            'channel' => fake()->randomElement(['in_store', 'whatsapp', 'phone']),
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->phoneNumber(),
            'payment_method' => fake()->randomElement(['cash_usd', 'pago_movil', 'pos_card']),
            'payment_reference' => fake()->numerify('REF-######'),
            'subtotal' => $subtotal,
            'discount_amount' => 0.00,
            'total_amount' => $subtotal,
            'notes' => fake()->optional()->sentence(),
            'sold_at' => now(),
        ];
    }
}
