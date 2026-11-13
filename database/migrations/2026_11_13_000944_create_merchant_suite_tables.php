<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('ai_enhanced_image_url', 500)->nullable();
        });

        Schema::create('mall_ad_banners', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignUuid('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('headline', 150);
            $table->string('subline', 255)->nullable();
            $table->string('badge', 60)->nullable();
            $table->string('tag', 60)->nullable();
            $table->string('image_url', 500);
            $table->string('target_url', 500)->nullable();
            $table->string('placement', 40)->default('hero_spotlight');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('clicks_count')->default(0);
            $table->unsignedInteger('impressions_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('store_sales', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignUuid('branch_id')->constrained('store_branches')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('sale_number', 40)->unique();
            $table->string('channel', 30)->default('in_store');
            $table->string('customer_name', 150)->nullable();
            $table->string('customer_phone', 50)->nullable();
            $table->string('payment_method', 40)->default('cash_usd');
            $table->string('payment_reference', 100)->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamp('sold_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('store_sale_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('store_sale_id')->constrained('store_sales')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('product_name', 200);
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_sale_items');
        Schema::dropIfExists('store_sales');
        Schema::dropIfExists('mall_ad_banners');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('ai_enhanced_image_url');
        });
    }
};
