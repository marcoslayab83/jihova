<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        $supportsSpatial = in_array($driver, ['mysql', 'mariadb'], true);

        Schema::create('stores', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('owner_id')->constrained('users')->restrictOnDelete();
            $table->string('business_name', 200);
            $table->string('slug', 200)->unique();
            $table->string('phone', 30);
            $table->enum('status', ['pending', 'active', 'suspended', 'closed_temporarily'])->default('pending');
            $table->decimal('commission_rate', 5, 2)->default(10.00);
            $table->decimal('rating', 2, 1)->default(5.0);
            $table->timestamps();
        });

        Schema::create('store_branches', function (Blueprint $table) use ($supportsSpatial) {
            $table->uuid('id')->primary();
            $table->foreignUuid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('branch_name', 150);
            $table->text('address');
            $location = $table->geography('location', subtype: 'point', srid: 4326);
            if (! $supportsSpatial) {
                $location->nullable();
            }
            $table->decimal('delivery_radius_km', 4, 2)->default(4.00);
            $table->unsignedInteger('avg_prep_time_minutes')->default(15);
            $table->boolean('is_open')->default(true);
            $table->timestamps();

            if ($supportsSpatial) {
                $table->spatialIndex('location');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_branches');
        Schema::dropIfExists('stores');
    }
};
