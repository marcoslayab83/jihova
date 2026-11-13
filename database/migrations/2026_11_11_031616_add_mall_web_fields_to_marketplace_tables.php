<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->default(0);
            $table->decimal('longitude', 10, 7)->default(0);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('description', 255)->nullable();
            $table->string('icon_name', 50)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->foreignUuid('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('tagline', 160)->nullable();
            $table->text('description')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('banner_url')->nullable();
            $table->string('accent_color', 20)->default('#4f46e5');
        });

        Schema::table('store_branches', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->default(0);
            $table->decimal('longitude', 10, 7)->default(0);
            $table->decimal('base_delivery_fee', 8, 2)->default(2.50);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('badge', 40)->nullable();
            $table->string('prep_note', 120)->nullable();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_reference', 100)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('customer_notes')->nullable();
            $table->json('delivery_snapshot')->nullable();
        });

        Schema::table('sub_orders', function (Blueprint $table) {
            $table->string('sub_order_code', 30)->nullable();
            $table->string('pickup_pin', 6)->nullable();
            $table->string('delivery_pin', 6)->nullable();
        });

        Schema::table('driver_profiles', function (Blueprint $table) {
            $table->decimal('current_latitude', 10, 7)->nullable();
            $table->decimal('current_longitude', 10, 7)->nullable();
            $table->unsignedInteger('completed_deliveries')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver_profiles', function (Blueprint $table) {
            $table->dropColumn(['current_latitude', 'current_longitude', 'completed_deliveries']);
        });

        Schema::table('sub_orders', function (Blueprint $table) {
            $table->dropColumn(['sub_order_code', 'pickup_pin', 'delivery_pin']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_reference', 'paid_at', 'customer_notes', 'delivery_snapshot']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['badge', 'prep_note']);
        });

        Schema::table('store_branches', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'base_delivery_fee']);
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['category_id', 'tagline', 'description', 'logo_url', 'banner_url', 'accent_color']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['description', 'icon_name', 'sort_order']);
        });

        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
