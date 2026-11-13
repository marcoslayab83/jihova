<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SubOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $unitPrice = 12.50;
        $qty = 2;

        return [
            'sub_order_id' => SubOrder::factory(),
            'product_id' => Product::factory(),
            'product_name' => 'Producto Demo',
            'unit_price' => $unitPrice,
            'quantity' => $qty,
            'total_price' => $unitPrice * $qty,
        ];
    }
}
