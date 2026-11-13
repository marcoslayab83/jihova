<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $items = 25.00;
        $delivery = 2.50;
        $platform = 0.75;

        return [
            'order_number' => 'CC-'.now()->format('Ymd').'-'.strtoupper(fake()->bothify('????')),
            'customer_id' => User::factory()->customer(),
            'address_id' => CustomerAddress::factory(),
            'total_items_amount' => $items,
            'total_delivery_fee' => $delivery,
            'platform_fee' => $platform,
            'grand_total' => $items + $delivery + $platform,
            'payment_status' => PaymentStatus::Paid,
            'payment_method' => 'card',
            'payment_reference' => 'PAY-'.strtoupper(fake()->bothify('########')),
            'paid_at' => now(),
            'customer_notes' => null,
            'delivery_snapshot' => [
                'title' => 'Casa',
                'street_address' => 'Av. Principal #10',
                'latitude' => 10.4880,
                'longitude' => -66.8792,
            ],
        ];
    }
}
