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

        Schema::create('customer_addresses', function (Blueprint $table) use ($supportsSpatial) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 50); // Casa, Trabajo, etc.
            $table->text('street_address');
            $table->text('reference')->nullable();
            // MySQL Spatial Point SRID 4326 (WGS 84 - GPS estándar)
            $coordinates = $table->geography('coordinates', subtype: 'point', srid: 4326);
            if (! $supportsSpatial) {
                $coordinates->nullable();
            }
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            if ($supportsSpatial) {
                $table->spatialIndex('coordinates');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
