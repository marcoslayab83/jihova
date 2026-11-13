<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_profiles', function (Blueprint $table) {
            $table->foreignUuid('user_id')->primary()->constrained('users')->cascadeOnDelete();
            $table->enum('vehicle_type', ['motorcycle', 'bicycle', 'car']);
            $table->string('license_plate', 20)->nullable();
            $table->geography('current_location', subtype: 'point', srid: 4326)->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_available')->default(true);
            $table->timestamp('last_ping_at')->nullable();

            $table->index(['is_active', 'is_available']);
        });

        Schema::create('delivery_tracking_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sub_order_id')->constrained('sub_orders')->cascadeOnDelete();
            $table->string('status', 40);
            $table->timestamp('event_timestamp')->useCurrent();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_tracking_events');
        Schema::dropIfExists('driver_profiles');
    }
};
