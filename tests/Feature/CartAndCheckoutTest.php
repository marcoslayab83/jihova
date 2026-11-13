<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Enums\SubOrderStatus;
use App\Models\BranchInventory;
use App\Models\Category;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreBranch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartAndCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_checkout_multi_store_cart_and_split_into_sub_orders(): void
    {
        $customer = User::factory()->customer()->create();
        $address = CustomerAddress::factory()->default()->create([
            'user_id' => $customer->id,
            'latitude' => 10.4880,
            'longitude' => -66.8792,
        ]);

        $category = Category::factory()->create();

        $cafe = Store::factory()->create(['category_id' => $category->id, 'commission_rate' => 10.00]);
        $cafeBranch = StoreBranch::factory()->create([
            'store_id' => $cafe->id,
            'latitude' => 10.4890,
            'longitude' => -66.8780,
            'delivery_radius_km' => 10.00,
            'base_delivery_fee' => 2.00,
        ]);
        $breakfast = Product::factory()->create(['store_id' => $cafe->id, 'category_id' => $category->id]);
        $cafeInv = BranchInventory::factory()->create([
            'branch_id' => $cafeBranch->id,
            'product_id' => $breakfast->id,
            'price' => 10.00,
            'stock_quantity' => 10,
        ]);

        $shoeStore = Store::factory()->create(['category_id' => $category->id, 'commission_rate' => 15.00]);
        $shoeBranch = StoreBranch::factory()->create([
            'store_id' => $shoeStore->id,
            'latitude' => 10.4900,
            'longitude' => -66.8770,
            'delivery_radius_km' => 10.00,
            'base_delivery_fee' => 3.00,
        ]);
        $shoes = Product::factory()->create(['store_id' => $shoeStore->id, 'category_id' => $category->id]);
        $shoeInv = BranchInventory::factory()->create([
            'branch_id' => $shoeBranch->id,
            'product_id' => $shoes->id,
            'price' => 50.00,
            'stock_quantity' => 5,
        ]);

        $this->actingAs($customer);

        $this->post('/carrito/items', [
            'branch_id' => $cafeBranch->id,
            'product_id' => $breakfast->id,
            'quantity' => 2,
        ])->assertRedirect();

        $this->post('/carrito/items', [
            'branch_id' => $shoeBranch->id,
            'product_id' => $shoes->id,
            'quantity' => 1,
        ])->assertRedirect();

        $response = $this->post('/checkout', [
            'address_id' => $address->id,
            'payment_method' => 'card',
            'card_holder' => 'Sofía Paredes',
            'card_number' => '4242424242424242',
            'card_expiry' => '12/29',
            'card_cvv' => '123',
        ]);

        $order = Order::query()->firstOrFail();
        $response->assertRedirect(route('orders.show', $order));

        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertSame('70.00', $order->total_items_amount);
        $this->assertCount(2, $order->subOrders);
        $this->assertTrue($order->subOrders->every(fn ($so): bool => $so->status === SubOrderStatus::Placed));

        $this->assertSame(8, $cafeInv->fresh()->stock_quantity);
        $this->assertSame(4, $shoeInv->fresh()->stock_quantity);
        $this->assertDatabaseCount('delivery_tracking_events', 2);
    }

    public function test_checkout_rejects_address_outside_branch_delivery_radius(): void
    {
        $customer = User::factory()->customer()->create();
        $farAddress = CustomerAddress::factory()->create([
            'user_id' => $customer->id,
            'latitude' => 10.9000,
            'longitude' => -67.5000,
        ]);

        $branch = StoreBranch::factory()->create([
            'latitude' => 10.4880,
            'longitude' => -66.8792,
            'delivery_radius_km' => 5.00,
        ]);
        $product = Product::factory()->create(['store_id' => $branch->store_id]);
        BranchInventory::factory()->create([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'stock_quantity' => 5,
        ]);

        $this->actingAs($customer);
        $this->post('/carrito/items', [
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->post('/checkout', [
            'address_id' => $farAddress->id,
            'payment_method' => 'cash_on_delivery',
        ])->assertSessionHasErrors('address_id');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_new_customer_can_checkout_with_inline_address_and_api_order_endpoint(): void
    {
        $customer = User::factory()->customer()->create();
        $category = Category::factory()->create();
        $store = Store::factory()->create(['category_id' => $category->id]);
        $branch = StoreBranch::factory()->create([
            'store_id' => $store->id,
            'latitude' => 10.4880,
            'longitude' => -66.8792,
            'delivery_radius_km' => 10.00,
        ]);
        $product = Product::factory()->create(['store_id' => $store->id, 'category_id' => $category->id]);
        BranchInventory::factory()->create([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'price' => 12.00,
            'stock_quantity' => 10,
        ]);

        $this->actingAs($customer);
        $this->post('/carrito/items', [
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertRedirect();

        $this->get('/carrito')->assertOk()->assertSee($product->name);
        $this->get('/checkout')->assertOk()->assertSee('Finalizar Compra');

        $this->post('/checkout', [
            'address_id' => '',
            'new_address' => [
                'title' => 'Departamento',
                'street_address' => 'Av. Principal 123',
                'reference' => 'Piso 2',
                'latitude' => 10.4890,
                'longitude' => -66.8780,
            ],
            'payment_method' => 'wallet',
        ])->assertRedirect();

        $address = CustomerAddress::query()->where('user_id', $customer->id)->firstOrFail();
        $order = Order::query()->where('customer_id', $customer->id)->firstOrFail();

        $this->get('/mis-pedidos')->assertOk()->assertSee($order->order_number);
        $this->get('/pedidos/'.$order->id)->assertOk()->assertSee($order->order_number);
        $this->get('/mi-cuenta/direcciones')->assertOk()->assertSee('Departamento');

        $this->postJson('/api/v1/orders', [
            'address_id' => $address->id,
            'payment_method' => 'mobile_pay',
            'items' => [
                ['branch_id' => $branch->id, 'product_id' => $product->id, 'quantity' => 2],
            ],
        ])->assertCreated()
            ->assertJsonPath('data.payment_status', 'paid');
    }
}
