<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Enums\SubOrderStatus;
use App\Models\BranchInventory;
use App\Models\Category;
use App\Models\CustomerAddress;
use App\Models\DriverProfile;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreBranch;
use App\Models\SubOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_merchant_prepares_and_driver_delivers_cash_on_delivery_sub_order(): void
    {
        $merchant = User::factory()->merchantAdmin()->create();
        $driver = User::factory()->driver()->create();
        $customer = User::factory()->customer()->create();

        $profile = DriverProfile::factory()->create(['user_id' => $driver->id]);
        $address = CustomerAddress::factory()->create(['user_id' => $customer->id]);

        $store = Store::factory()->create(['owner_id' => $merchant->id]);
        $branch = StoreBranch::factory()->create(['store_id' => $store->id]);
        $product = Product::factory()->create(['store_id' => $store->id, 'category_id' => $store->category_id]);
        BranchInventory::factory()->create(['branch_id' => $branch->id, 'product_id' => $product->id]);

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'address_id' => $address->id,
            'payment_status' => PaymentStatus::Unpaid,
            'payment_method' => 'cash_on_delivery',
            'paid_at' => null,
        ]);

        $subOrder = SubOrder::factory()->create([
            'order_id' => $order->id,
            'branch_id' => $branch->id,
            'status' => SubOrderStatus::Placed,
        ]);
        OrderItem::factory()->create(['sub_order_id' => $subOrder->id, 'product_id' => $product->id]);

        // Merchant advances through store workflow
        $this->actingAs($merchant);
        $this->post("/panel-tienda/sub-pedidos/{$subOrder->id}/estado", ['status' => 'accepted_by_store'])->assertRedirect();
        $this->post("/panel-tienda/sub-pedidos/{$subOrder->id}/estado", ['status' => 'preparing'])->assertRedirect();
        $this->post("/panel-tienda/sub-pedidos/{$subOrder->id}/estado", ['status' => 'ready_for_pickup'])->assertRedirect();

        $this->assertSame(SubOrderStatus::ReadyForPickup, $subOrder->fresh()->status);

        // Driver claims and completes delivery
        $this->actingAs($driver);
        $this->post("/panel-repartidor/envios/{$subOrder->id}/tomar")->assertRedirect();
        $this->assertSame($driver->id, $subOrder->fresh()->driver_id);
        $this->assertFalse($profile->fresh()->is_available);

        $this->post("/panel-repartidor/envios/{$subOrder->id}/avanzar", ['status' => 'out_for_delivery'])->assertRedirect();
        $this->post("/panel-repartidor/envios/{$subOrder->id}/avanzar", ['status' => 'delivered'])->assertRedirect();

        $this->assertSame(SubOrderStatus::Delivered, $subOrder->fresh()->status);
        $this->assertNotNull($subOrder->fresh()->delivered_at);
        $this->assertSame(1, $profile->fresh()->completed_deliveries);
        $this->assertTrue($profile->fresh()->is_available);
        $this->assertSame(PaymentStatus::Paid, $order->fresh()->payment_status);

        $this->actingAs($customer)
            ->getJson(route('orders.status', $order))
            ->assertOk()
            ->assertJsonFragment(['status' => 'delivered']);
    }

    public function test_merchant_can_publish_product_toggle_branch_and_auto_dispatch_driver(): void
    {
        $merchant = User::factory()->merchantAdmin()->create();
        $driver = User::factory()->driver()->create();
        $customer = User::factory()->customer()->create();
        $category = Category::factory()->create(['slug' => 'desayunos-cafe']);

        DriverProfile::factory()->create([
            'user_id' => $driver->id,
            'current_latitude' => 10.4885,
            'current_longitude' => -66.8790,
            'is_active' => true,
            'is_available' => true,
        ]);

        $address = CustomerAddress::factory()->create(['user_id' => $customer->id]);
        $store = Store::factory()->create(['owner_id' => $merchant->id, 'category_id' => $category->id]);
        $branch = StoreBranch::factory()->create([
            'store_id' => $store->id,
            'latitude' => 10.4880,
            'longitude' => -66.8792,
        ]);

        $this->actingAs($merchant);
        $this->get('/panel-tienda')->assertOk()->assertSee($store->business_name);

        $this->post('/panel-tienda/productos', [
            'store_id' => $store->id,
            'branch_id' => $branch->id,
            'category_id' => $category->id,
            'name' => 'Desayuno Especial Test',
            'description' => 'Huevos, pan y café.',
            'price' => 11.50,
            'stock_quantity' => 20,
        ])->assertRedirect();

        $this->assertDatabaseHas('products', ['name' => 'Desayuno Especial Test']);

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'address_id' => $address->id,
        ]);
        $subOrder = SubOrder::factory()->create([
            'order_id' => $order->id,
            'branch_id' => $branch->id,
            'status' => SubOrderStatus::ReadyForPickup,
        ]);

        $this->post("/panel-tienda/sub-pedidos/{$subOrder->id}/despachar")->assertRedirect();
        $this->assertSame($driver->id, $subOrder->fresh()->driver_id);
        $this->assertSame(SubOrderStatus::DriverAssigned, $subOrder->fresh()->status);

        $this->actingAs($driver);
        $this->get('/panel-repartidor')->assertOk()->assertSee($subOrder->sub_order_code);
        $this->patchJson('/api/v1/driver/location', [
            'latitude' => 10.4910,
            'longitude' => -66.8750,
        ])->assertOk();
    }
}
