<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table): void {
            $table->boolean('is_special_taxpayer')->default(false)->after('rating');
            $table->string('rif', 30)->nullable()->after('is_special_taxpayer');
            $table->decimal('bcv_rate', 12, 4)->default(50.0000)->after('rif');
        });

        Schema::table('store_branches', function (Blueprint $table): void {
            $table->boolean('pos_enabled')->default(true)->after('is_open');
            $table->string('invoice_printer_type', 20)->default('80mm')->after('pos_enabled');
        });

        Schema::create('cash_registers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('store_branch_id')->constrained('store_branches')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('status', 20)->default('active'); // active, inactive
            $table->timestamps();
        });

        Schema::create('cash_shifts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('cash_register_id')->constrained('cash_registers')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->decimal('opening_balance_usd', 12, 2)->default(0.00);
            $table->decimal('opening_balance_ves', 16, 2)->default(0.00);
            $table->decimal('expected_cash_usd', 12, 2)->default(0.00);
            $table->decimal('expected_cash_ves', 16, 2)->default(0.00);
            $table->decimal('actual_cash_usd', 12, 2)->nullable();
            $table->decimal('actual_cash_ves', 16, 2)->nullable();
            $table->decimal('difference_usd', 12, 2)->nullable();
            $table->decimal('difference_ves', 16, 2)->nullable();
            $table->string('status', 20)->default('open'); // open, closed
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['cash_register_id', 'status']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignUuid('address_id')->nullable()->change();
            $table->string('channel', 30)->default('online')->after('order_number'); // online, pos_counter
            $table->string('customer_doc', 50)->nullable()->after('customer_id'); // Cédula o RIF
            $table->string('customer_name', 150)->nullable()->after('customer_doc');
            $table->string('customer_phone', 50)->nullable()->after('customer_name');
            $table->foreignUuid('cash_shift_id')->nullable()->after('payment_method')->constrained('cash_shifts')->nullOnDelete();
            $table->decimal('igtf_amount', 12, 2)->default(0.00)->after('grand_total');
            $table->decimal('bcv_exchange_rate', 12, 4)->nullable()->after('igtf_amount');
            $table->string('payment_status', 30)->default('unpaid')->change();
        });

        Schema::table('sub_orders', function (Blueprint $table): void {
            $table->string('channel', 30)->default('online')->after('sub_order_code');
            $table->string('payment_status', 30)->default('unpaid')->after('status');
            $table->decimal('igtf_amount', 12, 2)->default(0.00)->after('commission_amount');
            $table->decimal('bcv_exchange_rate', 12, 4)->nullable()->after('igtf_amount');
        });

        Schema::create('payment_transactions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->nullable()->constrained('orders')->cascadeOnDelete();
            $table->foreignUuid('sub_order_id')->nullable()->constrained('sub_orders')->cascadeOnDelete();
            $table->foreignUuid('cash_shift_id')->nullable()->constrained('cash_shifts')->nullOnDelete();
            $table->string('method', 40); // efectivo_usd, efectivo_ves, pago_movil, punto_venta, zelle, jihova_wallet
            $table->string('currency', 10); // USD, VES
            $table->decimal('amount', 16, 2);
            $table->decimal('amount_usd_equivalent', 12, 2);
            $table->decimal('exchange_rate', 12, 4)->default(1.0000);
            $table->decimal('igtf_amount', 12, 2)->default(0.00);
            $table->string('reference_number', 100)->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->index(['order_id', 'method']);
            $table->index(['cash_shift_id', 'created_at']);
        });

        Schema::table('inventory_movements', function (Blueprint $table): void {
            $table->integer('quantity_delta')->default(0)->after('quantity');
            $table->unsignedInteger('balance_after')->default(0)->after('quantity_delta');
            $table->string('reference_type', 150)->nullable()->after('balance_after');
            $table->uuid('reference_id')->nullable()->after('reference_type');
            $table->string('type', 30)->change();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table): void {
            $table->dropColumn(['quantity_delta', 'balance_after', 'reference_type', 'reference_id']);
        });

        Schema::dropIfExists('payment_transactions');

        Schema::table('sub_orders', function (Blueprint $table): void {
            $table->dropColumn(['channel', 'payment_status', 'igtf_amount', 'bcv_exchange_rate']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['cash_shift_id']);
            $table->dropColumn(['channel', 'customer_doc', 'customer_name', 'customer_phone', 'cash_shift_id', 'igtf_amount', 'bcv_exchange_rate']);
        });

        Schema::dropIfExists('cash_shifts');
        Schema::dropIfExists('cash_registers');

        Schema::table('store_branches', function (Blueprint $table): void {
            $table->dropColumn(['pos_enabled', 'invoice_printer_type']);
        });

        Schema::table('stores', function (Blueprint $table): void {
            $table->dropColumn(['is_special_taxpayer', 'rif', 'bcv_rate']);
        });
    }
};
