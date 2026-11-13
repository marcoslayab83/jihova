<?php

namespace Database\Factories;

use App\Enums\SubOrderStatus;
use App\Models\Order;
use App\Models\StoreBranch;
use App\Models\SubOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubOrder>
 */
class SubOrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'sub_order_code' => 'SUB-'.strtoupper(fake()->bothify('######')),
            'branch_id' => StoreBranch::factory(),
            'driver_id' => null,
            'status' => SubOrderStatus::Placed,
            'subtotal' => 25.00,
            'delivery_fee' => 2.50,
            'commission_amount' => 2.50,
            'pickup_pin' => (string) fake()->numberBetween(1000, 9999),
            'delivery_pin' => (string) fake()->numberBetween(1000, 9999),
            'estimated_delivery_at' => now()->addMinutes(35),
            'delivered_at' => null,
        ];
    }
}
