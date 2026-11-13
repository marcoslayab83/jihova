<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        $supportsFullText = in_array($driver, ['mysql', 'mariadb', 'pgsql'], true);

        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('icon_url')->nullable();
        });

        Schema::create('products', function (Blueprint $table) use ($supportsFullText) {
            $table->uuid('id')->primary();
            $table->foreignUuid('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignUuid('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('name', 200);
            $table->string('slug', 220);
            $table->text('description')->nullable();
            $table->string('sku', 100)->nullable();
            $table->decimal('base_price', 10, 2);
            $table->string('image_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['store_id', 'slug']);
            if ($supportsFullText) {
                $table->fullText(['name', 'description']);
            }
        });

        Schema::create('branch_inventory', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained('store_branches')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('price', 10, 2);
            $table->integer('stock_quantity')->default(0);
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->unique(['branch_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_inventory');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
