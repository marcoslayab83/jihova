<?php

namespace Tests\Feature;

use App\Enums\StoreStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\FiscalInvoice;
use App\Models\FiscalPurchase;
use App\Models\Store;
use App\Models\StoreBranch;
use App\Models\StoreUser;
use App\Models\User;
use App\Models\WithholdingVoucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiTenantAndFiscalAuthTest extends TestCase
{
    use RefreshDatabase;

    protected Category $category;
    protected User $merchantA;
    protected User $merchantB;
    protected User $cashierA;
    protected User $admin;
    protected User $customer;
    protected Store $storeA;
    protected Store $storeB;
    protected StoreBranch $branchA;
    protected StoreBranch $branchB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'name' => 'Alimentos y Bebidas',
            'slug' => 'alimentos-bebidas',
            'description' => 'Comestibles',
        ]);

        $this->merchantA = User::factory()->create([
            'role' => UserRole::MerchantAdmin,
            'full_name' => 'Carlos Dueño A',
        ]);

        $this->merchantB = User::factory()->create([
            'role' => UserRole::MerchantAdmin,
            'full_name' => 'Elena Dueña B',
        ]);

        $this->cashierA = User::factory()->create([
            'role' => UserRole::MerchantStaff,
            'full_name' => 'Cajero Pedro A',
        ]);

        $this->admin = User::factory()->create([
            'role' => UserRole::PlatformAdmin,
            'full_name' => 'Admin Global JIHOVA',
        ]);

        $this->customer = User::factory()->create([
            'role' => UserRole::Customer,
            'full_name' => 'Cliente Comprador',
        ]);

        // Tienda A
        $this->storeA = Store::create([
            'owner_id' => $this->merchantA->id,
            'category_id' => $this->category->id,
            'business_name' => 'Supermercado Central A',
            'legal_name' => 'Supermercado Central A, C.A.',
            'slug' => 'supermercado-central-a',
            'rif' => 'J-12345678-0',
            'iva_condition' => 'contribuyente_especial',
            'is_special_taxpayer' => true,
            'phone' => '+584121111111',
            'status' => StoreStatus::Active,
            'commission_rate' => 10.00,
            'bcv_rate' => 50.0000,
        ]);

        $this->branchA = StoreBranch::create([
            'store_id' => $this->storeA->id,
            'branch_name' => 'Sede Central A',
            'address' => 'Av. Bolívar A',
            'latitude' => 10.24,
            'longitude' => -67.59,
            'delivery_radius_km' => 5.0,
            'base_delivery_fee' => 2.0,
            'avg_prep_time_minutes' => 15,
            'is_open' => true,
            'pos_enabled' => true,
        ]);

        // Asignar cajero en Store A
        StoreUser::create([
            'store_id' => $this->storeA->id,
            'user_id' => $this->cashierA->id,
            'role' => 'cashier',
            'store_branch_id' => $this->branchA->id,
            'is_active' => true,
        ]);

        // Tienda B
        $this->storeB = Store::create([
            'owner_id' => $this->merchantB->id,
            'category_id' => $this->category->id,
            'business_name' => 'Farmacia Vida B',
            'legal_name' => 'Farmacia Vida B, C.A.',
            'slug' => 'farmacia-vida-b',
            'rif' => 'J-98765432-1',
            'iva_condition' => 'ordinario',
            'is_special_taxpayer' => false,
            'phone' => '+584142222222',
            'status' => StoreStatus::Active,
            'commission_rate' => 10.00,
            'bcv_rate' => 50.0000,
        ]);

        $this->branchB = StoreBranch::create([
            'store_id' => $this->storeB->id,
            'branch_name' => 'Sede Farmacia B',
            'address' => 'Av. Casanova B',
            'latitude' => 10.25,
            'longitude' => -67.60,
            'delivery_radius_km' => 5.0,
            'base_delivery_fee' => 2.0,
            'avg_prep_time_minutes' => 15,
            'is_open' => true,
            'pos_enabled' => true,
        ]);
    }

    public function test_merchant_can_access_their_own_store_but_is_denied_from_other_stores(): void
    {
        // Merchant A accede al POS de su tienda A -> 200 OK
        $responseA = $this->actingAs($this->merchantA)
            ->get(route('merchant.pos.index', ['branch_id' => $this->branchA->id]));
        $responseA->assertStatus(200);
        $responseA->assertSee('Supermercado Central A');

        // Merchant A intenta acceder a la sede de la tienda B -> 404/403 Aislamiento Multi-Tenant
        $responseB = $this->actingAs($this->merchantA)
            ->get(route('merchant.pos.index', ['branch_id' => $this->branchB->id]));
        $this->assertTrue(in_array($responseB->status(), [403, 404], true));

        // Policy checks
        $this->assertTrue($this->merchantA->canAccessStore($this->storeA));
        $this->assertFalse($this->merchantA->canAccessStore($this->storeB));
        $this->assertTrue($this->merchantA->hasStoreRole($this->storeA, 'owner'));
        $this->assertFalse($this->merchantA->hasStoreRole($this->storeB, 'owner'));
    }

    public function test_cashier_can_access_pos_of_assigned_store_via_store_users_pivot(): void
    {
        // Cajero de Tienda A tiene acceso al POS de Tienda A
        $response = $this->actingAs($this->cashierA)
            ->get(route('merchant.pos.index', ['branch_id' => $this->branchA->id]));
        $response->assertStatus(200);
        $response->assertSee('Supermercado Central A');

        // Cajero no tiene acceso a Tienda B
        $this->assertFalse($this->cashierA->canAccessStore($this->storeB));
    }

    public function test_platform_admin_has_global_access_to_all_stores(): void
    {
        $this->assertTrue($this->admin->canAccessStore($this->storeA));
        $this->assertTrue($this->admin->canAccessStore($this->storeB));
        $this->assertTrue($this->admin->hasStoreRole($this->storeA, 'owner'));
    }

    public function test_can_register_new_merchant_with_venezuelan_fiscal_fields_in_pending_status(): void
    {
        $registrationData = [
            'full_name' => 'Alejandro Morales',
            'email' => 'gerencia@panaderiaelparaiso.com',
            'phone' => '+584129998877',
            'password' => 'Secreto1234#',
            'password_confirmation' => 'Secreto1234#',
            'business_name' => 'Panadería El Paraíso',
            'legal_name' => 'Panadería y Pastelería El Paraíso, C.A.',
            'rif' => 'J-40112233-4',
            'iva_condition' => 'contribuyente_especial',
            'category_id' => $this->category->id,
            'fiscal_address' => 'Urb. El Paraíso, Calle 3, Local 12, Caracas',
            'branch_name' => 'Sede Principal El Paraíso',
            'branch_address' => 'Av. Páez, frente a la plaza',
            'branch_phone' => '+582124556677',
        ];

        $response = $this->post(route('merchant.register.store'), $registrationData);

        $response->assertRedirect(route('merchant.dashboard'));
        $this->assertAuthenticated();

        // Verificar que la tienda se creó en estado PENDING
        $store = Store::where('rif', 'J-40112233-4')->first();
        $this->assertNotNull($store);
        $this->assertEquals(StoreStatus::Pending, $store->status);
        $this->assertEquals('Panadería y Pastelería El Paraíso, C.A.', $store->legal_name);
        $this->assertTrue($store->is_special_taxpayer);

        // Verificar que el usuario quedó asignado como OWNER en store_users
        $membership = StoreUser::where('store_id', $store->id)->first();
        $this->assertNotNull($membership);
        $this->assertEquals('owner', $membership->role);
        $this->assertTrue($membership->is_active);
    }

    public function test_can_create_fiscal_invoices_and_withholding_vouchers_with_bimonetary_records(): void
    {
        // 1. Crear Factura Fiscal SENIAT Bimonetaria
        $invoice = FiscalInvoice::create([
            'store_id' => $this->storeA->id,
            'store_branch_id' => $this->branchA->id,
            'user_id' => $this->merchantA->id,
            'document_type' => 'invoice',
            'correlative_number' => 'FAC-00000001',
            'control_number' => '00-00001001',
            'customer_rif_doc' => 'V-14555666',
            'customer_name' => 'Maria Rodriguez',
            'customer_address' => 'Maracay Centro',
            'exchange_rate' => 50.0000,
            'amount_taxable_usd' => 10.00,
            'amount_taxable_ves' => 500.00,
            'vat_percentage' => 16.00,
            'vat_amount_usd' => 1.60,
            'vat_amount_ves' => 80.00,
            'igtf_percentage' => 3.00,
            'igtf_amount_usd' => 0.35, // 3% sobre divisas
            'igtf_amount_ves' => 17.50,
            'total_amount_usd' => 11.95,
            'total_amount_ves' => 597.50,
            'status' => 'issued',
            'issued_at' => now(),
        ]);

        $this->assertNotNull($invoice->id);
        $this->assertEquals('Factura Fiscal', $invoice->documentTypeLabel());
        $this->assertEquals(11.95, (float) $invoice->total_amount_usd);
        $this->assertEquals(597.50, (float) $invoice->total_amount_ves);

        // 2. Crear Compra a Proveedor (Libro de Compras SENIAT)
        $purchase = FiscalPurchase::create([
            'store_id' => $this->storeA->id,
            'user_id' => $this->merchantA->id,
            'supplier_rif' => 'J-30111222-9',
            'supplier_name' => 'Distribuidora Harinas Polar, C.A.',
            'invoice_number' => '00054321',
            'control_number' => '00-009988',
            'purchase_date' => now()->toDateString(),
            'exchange_rate' => 50.0000,
            'amount_taxable_usd' => 100.00,
            'amount_taxable_ves' => 5000.00,
            'vat_percentage' => 16.00,
            'vat_amount_usd' => 16.00,
            'vat_amount_ves' => 800.00,
            'total_amount_usd' => 116.00,
            'total_amount_ves' => 5800.00,
            'vat_withheld_amount_usd' => 12.00, // 75% del IVA
            'vat_withheld_amount_ves' => 600.00,
        ]);

        $this->assertNotNull($purchase->id);
        $this->assertEquals(600.00, (float) $purchase->vat_withheld_amount_ves);

        // 3. Crear Comprobante de Retención de IVA
        $voucher = WithholdingVoucher::create([
            'store_id' => $this->storeA->id,
            'fiscal_purchase_id' => $purchase->id,
            'voucher_type' => 'iva',
            'voucher_number' => '20261100000001',
            'period_year_month' => '2026-11',
            'voucher_date' => now()->toDateString(),
            'subject_rif' => 'J-30111222-9',
            'subject_name' => 'Distribuidora Harinas Polar, C.A.',
            'exchange_rate' => 50.0000,
            'taxable_amount_ves' => 5000.00,
            'tax_amount_ves' => 800.00,
            'withholding_percentage' => 75.00,
            'withheld_amount_ves' => 600.00,
            'withheld_amount_usd' => 12.00,
        ]);

        $this->assertNotNull($voucher->id);
        $this->assertEquals('20261100000001', $voucher->voucher_number);
    }
}
