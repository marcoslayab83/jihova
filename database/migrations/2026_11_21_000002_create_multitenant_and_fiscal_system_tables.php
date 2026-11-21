<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ampliación de la tabla stores con datos fiscales y jurídicos de Venezuela
        Schema::table('stores', function (Blueprint $table) {
            if (! Schema::hasColumn('stores', 'legal_name')) {
                $table->string('legal_name', 255)->nullable()->after('business_name');
            }
            if (! Schema::hasColumn('stores', 'iva_condition')) {
                $table->string('iva_condition', 40)->default('ordinario')->after('is_special_taxpayer'); // ordinario, contribuyente_especial, formal
            }
            if (! Schema::hasColumn('stores', 'fiscal_address')) {
                $table->text('fiscal_address')->nullable()->after('legal_name');
            }
            if (! Schema::hasColumn('stores', 'seniat_invoice_correlative')) {
                $table->unsignedBigInteger('seniat_invoice_correlative')->default(1)->after('bcv_rate');
            }
            if (! Schema::hasColumn('stores', 'seniat_control_correlative')) {
                $table->unsignedBigInteger('seniat_control_correlative')->default(1)->after('seniat_invoice_correlative');
            }
        });

        // 2. Tabla Pivote Multi-Tenant: store_users (Personal y Sub-roles por Tienda)
        if (! Schema::hasTable('store_users')) {
            Schema::create('store_users', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('store_id')->constrained('stores')->cascadeOnDelete();
                $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('role', 30)->default('cashier'); // owner, manager, cashier, accountant
                $table->foreignUuid('store_branch_id')->nullable()->constrained('store_branches')->nullOnDelete();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['store_id', 'user_id']);
                $table->index(['store_id', 'role']);
                $table->index(['user_id', 'role']);
            });
        }

        // 3. Facturación y Documentos Fiscales SENIAT (Libro de Ventas y Bimonetario Inmutable)
        if (! Schema::hasTable('fiscal_invoices')) {
            Schema::create('fiscal_invoices', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('store_id')->constrained('stores')->cascadeOnDelete();
                $table->foreignUuid('store_branch_id')->nullable()->constrained('store_branches')->nullOnDelete();
                $table->foreignUuid('order_id')->nullable()->constrained('orders')->nullOnDelete();
                $table->foreignUuid('sub_order_id')->nullable()->constrained('sub_orders')->nullOnDelete();
                $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete(); // Cajero / Emisor

                // Tipología y Control Fiscal SENIAT
                $table->string('document_type', 30)->default('invoice'); // invoice (Factura), credit_note, debit_note, delivery_note
                $table->string('correlative_number', 50); // Número de Factura consecutivo (ej. FAC-00001001)
                $table->string('control_number', 50)->nullable(); // N° de Control fiscal impreso/asignado (ej. 00-00004521)
                $table->string('affected_document_number', 50)->nullable(); // En notas de crédito/débito que modifican una factura previa

                // Datos Fiscales del Cliente / Receptor
                $table->string('customer_rif_doc', 30); // V-, J-, G-, E-
                $table->string('customer_name', 150);
                $table->text('customer_address')->nullable();
                $table->string('customer_phone', 30)->nullable();

                // Registro Bimonetario Inmutable (Tasa BCV y Montos USD y VES)
                $table->decimal('exchange_rate', 12, 4); // Tasa oficial BCV a la fecha de emisión
                $table->decimal('amount_taxable_usd', 12, 2)->default(0.00);
                $table->decimal('amount_taxable_ves', 16, 2)->default(0.00);
                $table->decimal('amount_exempt_usd', 12, 2)->default(0.00);
                $table->decimal('amount_exempt_ves', 16, 2)->default(0.00);

                // IVA (16% Alícuota General o 8% Reducida)
                $table->decimal('vat_percentage', 5, 2)->default(16.00);
                $table->decimal('vat_amount_usd', 12, 2)->default(0.00);
                $table->decimal('vat_amount_ves', 16, 2)->default(0.00);

                // IGTF (3% únicamente sobre divisas en efectivo / Zelle para contribuyentes especiales)
                $table->decimal('igtf_percentage', 5, 2)->default(3.00);
                $table->decimal('igtf_amount_usd', 12, 2)->default(0.00);
                $table->decimal('igtf_amount_ves', 16, 2)->default(0.00);

                // Totales
                $table->decimal('total_amount_usd', 12, 2);
                $table->decimal('total_amount_ves', 16, 2);

                // Estado y Metadatos
                $table->string('status', 20)->default('issued'); // issued, annulled
                $table->timestamp('issued_at')->useCurrent();
                $table->timestamp('annulled_at')->nullable();
                $table->text('annulment_reason')->nullable();
                $table->timestamps();

                $table->unique(['store_id', 'correlative_number', 'document_type']);
                $table->index(['store_id', 'issued_at']);
                $table->index(['store_id', 'document_type']);
            });
        }

        // 4. Libro de Compras SENIAT (Facturas Recibidas de Proveedores)
        if (! Schema::hasTable('fiscal_purchases')) {
            Schema::create('fiscal_purchases', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('store_id')->constrained('stores')->cascadeOnDelete();
                $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete(); // Contador o usuario que registra

                // Datos del Proveedor y Factura
                $table->string('supplier_rif', 30);
                $table->string('supplier_name', 150);
                $table->string('invoice_number', 50);
                $table->string('control_number', 50)->nullable();
                $table->date('purchase_date');

                // Bimonetario Inmutable
                $table->decimal('exchange_rate', 12, 4);
                $table->decimal('amount_taxable_usd', 12, 2)->default(0.00);
                $table->decimal('amount_taxable_ves', 16, 2)->default(0.00);
                $table->decimal('amount_exempt_usd', 12, 2)->default(0.00);
                $table->decimal('amount_exempt_ves', 16, 2)->default(0.00);
                $table->decimal('vat_percentage', 5, 2)->default(16.00);
                $table->decimal('vat_amount_usd', 12, 2)->default(0.00);
                $table->decimal('vat_amount_ves', 16, 2)->default(0.00);
                $table->decimal('total_amount_usd', 12, 2);
                $table->decimal('total_amount_ves', 16, 2);

                // Retenciones Aplicadas al Proveedor
                $table->decimal('vat_withheld_amount_usd', 12, 2)->default(0.00);
                $table->decimal('vat_withheld_amount_ves', 16, 2)->default(0.00);
                $table->decimal('islr_withheld_amount_usd', 12, 2)->default(0.00);
                $table->decimal('islr_withheld_amount_ves', 16, 2)->default(0.00);

                $table->timestamps();

                $table->index(['store_id', 'purchase_date']);
                $table->index(['supplier_rif']);
            });
        }

        // 5. Comprobantes de Retención de IVA e ISLR (SENIAT)
        if (! Schema::hasTable('withholding_vouchers')) {
            Schema::create('withholding_vouchers', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('store_id')->constrained('stores')->cascadeOnDelete();
                $table->foreignUuid('fiscal_purchase_id')->nullable()->constrained('fiscal_purchases')->nullOnDelete();
                $table->foreignUuid('fiscal_invoice_id')->nullable()->constrained('fiscal_invoices')->nullOnDelete();

                $table->string('voucher_type', 20)->default('iva'); // iva, islr
                $table->string('voucher_number', 50); // Correlativo fiscal AAAAMM00000001
                $table->string('period_year_month', 7); // ej. 2026-11
                $table->date('voucher_date');

                // Sujeto Retenido (Proveedor o Cliente)
                $table->string('subject_rif', 30);
                $table->string('subject_name', 150);

                // Montos y Porcentajes
                $table->decimal('exchange_rate', 12, 4);
                $table->decimal('taxable_amount_ves', 16, 2);
                $table->decimal('tax_amount_ves', 16, 2);
                $table->decimal('withholding_percentage', 5, 2); // 75%, 100%, etc.
                $table->decimal('withheld_amount_ves', 16, 2);
                $table->decimal('withheld_amount_usd', 12, 2);

                $table->timestamps();

                $table->unique(['store_id', 'voucher_type', 'voucher_number']);
                $table->index(['store_id', 'period_year_month']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('withholding_vouchers');
        Schema::dropIfExists('fiscal_purchases');
        Schema::dropIfExists('fiscal_invoices');
        Schema::dropIfExists('store_users');

        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn([
                'legal_name',
                'iva_condition',
                'fiscal_address',
                'seniat_invoice_correlative',
                'seniat_control_correlative',
            ]);
        });
    }
};
