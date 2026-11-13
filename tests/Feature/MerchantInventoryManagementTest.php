<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Enums\SubOrderStatus;
use App\Models\BranchInventory;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreBranch;
use App\Models\StoreSale;
use App\Models\StoreSaleItem;
use App\Models\SubOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MerchantInventoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_merchant_can_register_inventory_entries_and_rejects_an_exit_that_exceeds_stock(): void
    {
        $merchant = User::factory()->merchantAdmin()->create();
        $store = Store::factory()->create(['owner_id' => $merchant->id]);
        $branch = StoreBranch::factory()->create(['store_id' => $store->id]);
        $product = Product::factory()->create(['store_id' => $store->id]);
        $inventory = BranchInventory::factory()->create([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'stock_quantity' => 3,
        ]);

        $this->actingAs($merchant)
            ->post(route('merchant.inventory-movements.store'), [
                'branch_id' => $branch->id,
                'product_id' => $product->id,
                'type' => 'entry',
                'quantity' => 2,
                'reason' => 'Compra de proveedor',
            ])
            ->assertRedirect();

        $this->assertSame(5, $inventory->fresh()->stock_quantity);
        $this->assertDatabaseHas('inventory_movements', [
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'type' => 'entry',
            'quantity' => 2,
            'stock_before' => 3,
            'stock_after' => 5,
            'reason' => 'Compra de proveedor',
        ]);

        $this->post(route('merchant.inventory-movements.store'), [
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'type' => 'exit',
            'quantity' => 6,
            'reason' => 'Merma',
        ])->assertSessionHasErrors('quantity');

        $this->assertSame(5, $inventory->fresh()->stock_quantity);
        $this->assertDatabaseCount('inventory_movements', 1);
    }

    public function test_product_pricing_updates_each_branch_and_sales_snapshot_cost_and_stock(): void
    {
        $merchant = User::factory()->merchantAdmin()->create();
        $category = Category::factory()->create();
        $store = Store::factory()->create([
            'owner_id' => $merchant->id,
            'category_id' => $category->id,
        ]);
        $firstBranch = StoreBranch::factory()->create(['store_id' => $store->id]);
        $secondBranch = StoreBranch::factory()->create(['store_id' => $store->id]);
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'cost_price' => 10,
            'base_price' => 10,
        ]);
        $firstInventory = BranchInventory::factory()->create([
            'branch_id' => $firstBranch->id,
            'product_id' => $product->id,
            'price' => 10,
            'stock_quantity' => 8,
        ]);
        $secondInventory = BranchInventory::factory()->create([
            'branch_id' => $secondBranch->id,
            'product_id' => $product->id,
            'price' => 10,
            'stock_quantity' => 4,
        ]);

        $this->actingAs($merchant)
            ->patch(route('merchant.products.pricing', $product), [
                'cost_price' => 12,
                'profit_margin_percent' => 50,
                'discount_percent' => 20,
            ])
            ->assertRedirect();

        $this->assertSame('12.00', $product->fresh()->cost_price);
        $this->assertSame('18.00', $product->fresh()->base_price);
        $this->assertSame('14.40', $firstInventory->fresh()->price);
        $this->assertSame('14.40', $secondInventory->fresh()->price);

        $this->post(route('merchant.sales.store'), [
            'branch_id' => $firstBranch->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
            'channel' => 'in_store',
            'payment_method' => 'pos_card',
        ])->assertRedirect();

        $this->assertDatabaseHas('store_sales', [
            'branch_id' => $firstBranch->id,
            'subtotal' => '36.00',
            'discount_amount' => '7.20',
            'total_amount' => '28.80',
        ]);
        $this->assertDatabaseHas('store_sale_items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => '14.40',
            'unit_cost' => '12.00',
            'total_price' => '28.80',
        ]);
        $this->assertSame(6, $firstInventory->fresh()->stock_quantity);
        $this->assertDatabaseHas('inventory_movements', [
            'branch_id' => $firstBranch->id,
            'product_id' => $product->id,
            'type' => 'exit',
            'quantity' => 2,
            'stock_before' => 8,
            'stock_after' => 6,
        ]);
    }

    public function test_sales_reports_include_paid_delivered_online_orders_and_in_store_sales_for_day_and_month(): void
    {
        $merchant = User::factory()->merchantAdmin()->create();
        $customer = User::factory()->customer()->create();
        $category = Category::factory()->create();
        $store = Store::factory()->create([
            'owner_id' => $merchant->id,
            'category_id' => $category->id,
            'commission_rate' => 10,
        ]);
        $branch = StoreBranch::factory()->create(['store_id' => $store->id]);
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'cost_price' => 4,
            'base_price' => 10,
        ]);
        BranchInventory::factory()->create([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'price' => 10,
            'stock_quantity' => 10,
        ]);

        $this->actingAs($merchant)
            ->post(route('merchant.sales.store'), [
                'branch_id' => $branch->id,
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 1],
                ],
                'channel' => 'in_store',
                'payment_method' => 'cash_usd',
            ])
            ->assertRedirect();

        $onlineOrder = Order::factory()->create([
            'customer_id' => $customer->id,
            'payment_status' => PaymentStatus::Paid,
        ]);
        $onlineSubOrder = SubOrder::factory()->create([
            'order_id' => $onlineOrder->id,
            'branch_id' => $branch->id,
            'status' => SubOrderStatus::Delivered,
            'subtotal' => 20,
            'commission_amount' => 2,
            'delivered_at' => now(),
        ]);
        OrderItem::create([
            'sub_order_id' => $onlineSubOrder->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => 10,
            'unit_cost' => 4,
            'quantity' => 2,
            'total_price' => 20,
        ]);

        $unpaidOrder = Order::factory()->create([
            'payment_status' => PaymentStatus::Unpaid,
        ]);
        SubOrder::factory()->create([
            'order_id' => $unpaidOrder->id,
            'branch_id' => $branch->id,
            'status' => SubOrderStatus::Placed,
            'subtotal' => 100,
        ]);

        $earlierSaleThisMonth = StoreSale::factory()->create([
            'store_id' => $store->id,
            'branch_id' => $branch->id,
            'user_id' => $merchant->id,
            'subtotal' => 10,
            'total_amount' => 10,
            'sold_at' => now()->startOfMonth()->addDay(),
        ]);
        StoreSaleItem::factory()->create([
            'store_sale_id' => $earlierSaleThisMonth->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 10,
            'unit_cost' => 4,
            'total_price' => 10,
        ]);

        $this->get(route('merchant.dashboard'))
            ->assertOk()
            ->assertSee('$30.00')
            ->assertSee('$16.00')
            ->assertSee('$40.00')
            ->assertSee('$22.00');
    }

    public function test_merchant_cannot_register_inventory_movements_for_another_store(): void
    {
        $merchant = User::factory()->merchantAdmin()->create();
        $otherMerchant = User::factory()->merchantAdmin()->create();
        $store = Store::factory()->create(['owner_id' => $otherMerchant->id]);
        $branch = StoreBranch::factory()->create(['store_id' => $store->id]);
        $product = Product::factory()->create(['store_id' => $store->id]);
        $inventory = BranchInventory::factory()->create([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'stock_quantity' => 3,
        ]);

        $this->actingAs($merchant)
            ->post(route('merchant.inventory-movements.store'), [
                'branch_id' => $branch->id,
                'product_id' => $product->id,
                'type' => 'entry',
                'quantity' => 2,
                'reason' => 'Compra',
            ])
            ->assertForbidden();

        $this->assertSame(3, $inventory->fresh()->stock_quantity);
        $this->assertDatabaseCount('inventory_movements', 0);
    }
}
