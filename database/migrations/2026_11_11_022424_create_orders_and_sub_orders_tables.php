<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_number', 30)->unique();
            $table->foreignUuid('customer_id')->constrained('users')->restrictOnDelete();
            $table->foreignUuid('address_id')->constrained('customer_addresses')->restrictOnDelete();
            $table->decimal('total_items_amount', 10, 2);
            $table->decimal('total_delivery_fee', 10, 2);
            $table->decimal('platform_fee', 10, 2)->default(0.00);
            $table->decimal('grand_total', 10, 2);
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded', 'failed'])->default('unpaid');
            $table->string('payment_method', 50);
            $table->timestamps();
        });

        Schema::create('sub_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignUuid('branch_id')->constrained('store_branches')->restrictOnDelete();
            $table->foreignUuid('driver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', [
                'placed', 'accepted_by_store', 'preparing', 'ready_for_pickup',
                'driver_assigned', 'out_for_delivery', 'delivered', 'cancelled',
            ])->default('placed');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_fee', 10, 2);
            $table->decimal('commission_amount', 10, 2);
            $table->timestamp('estimated_delivery_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sub_order_id')->constrained('sub_orders')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
            $table->string('product_name', 200);
            $table->decimal('unit_price', 10, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('total_price', 10, 2);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('sub_orders');
        Schema::dropIfExists('orders');
    }
};
