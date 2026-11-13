<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->decimal('profit_margin_percent', 7, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->decimal('unit_cost', 10, 2)->nullable();
        });

        Schema::table('store_sale_items', function (Blueprint $table): void {
            $table->decimal('unit_cost', 10, 2)->nullable();
        });

        Schema::create('inventory_movements', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained('store_branches')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 10);
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('stock_before');
            $table->unsignedInteger('stock_after');
            $table->string('reason', 255);
            $table->timestamps();

            $table->index(['branch_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');

        Schema::table('store_sale_items', function (Blueprint $table): void {
            $table->dropColumn('unit_cost');
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropColumn('unit_cost');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['cost_price', 'profit_margin_percent', 'discount_percent']);
        });
    }
};
