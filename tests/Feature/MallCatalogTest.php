<?php

namespace Tests\Feature;

use App\Enums\StoreStatus;
use App\Models\BranchInventory;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreBranch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MallCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_and_explore_search_find_breakfast_and_shoes_across_stores(): void
    {
        $foodCategory = Category::factory()->create(['name' => 'Desayunos', 'slug' => 'desayunos']);
        $shoeCategory = Category::factory()->create(['name' => 'Calzado', 'slug' => 'calzado']);

        $cafe = Store::factory()->create([
            'category_id' => $foodCategory->id,
            'business_name' => 'Aurora Café',
            'slug' => 'aurora-cafe',
            'status' => StoreStatus::Active,
        ]);
        $cafeBranch = StoreBranch::factory()->create(['store_id' => $cafe->id, 'is_open' => true]);
        $breakfast = Product::factory()->create([
            'store_id' => $cafe->id,
            'category_id' => $foodCategory->id,
            'name' => 'Desayuno Criollo Especial',
            'slug' => 'desayuno-criollo-especial',
        ]);
        BranchInventory::factory()->create([
            'branch_id' => $cafeBranch->id,
            'product_id' => $breakfast->id,
            'price' => 9.50,
            'stock_quantity' => 15,
        ]);

        $shoeStore = Store::factory()->create([
            'category_id' => $shoeCategory->id,
            'business_name' => 'StepUp Calzado',
            'slug' => 'stepup-calzado',
            'status' => StoreStatus::Active,
        ]);
        $shoeBranch = StoreBranch::factory()->create(['store_id' => $shoeStore->id, 'is_open' => true]);
        $shoes = Product::factory()->create([
            'store_id' => $shoeStore->id,
            'category_id' => $shoeCategory->id,
            'name' => 'Zapatos Running AirPulse',
            'slug' => 'zapatos-running-airpulse',
        ]);
        BranchInventory::factory()->create([
            'branch_id' => $shoeBranch->id,
            'product_id' => $shoes->id,
            'price' => 68.00,
            'stock_quantity' => 8,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Aurora Café')
            ->assertSee('StepUp Calzado');

        $this->get('/explorar?q=Desayuno')
            ->assertOk()
            ->assertSee('Desayuno Criollo Especial')
            ->assertDontSee('Zapatos Running AirPulse');

        $this->get('/explorar?q=Zapatos')
            ->assertOk()
            ->assertSee('Zapatos Running AirPulse')
            ->assertDontSee('Desayuno Criollo Especial');

        $this->get('/tiendas/stepup-calzado')
            ->assertOk()
            ->assertSee('Zapatos Running AirPulse');

        $this->get('/producto/aurora-cafe/desayuno-criollo-especial')
            ->assertOk()
            ->assertSee('Desayuno Criollo Especial');

        $this->getJson('/api/v1/search?q=Zapatos')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Zapatos Running AirPulse']);
    }

    public function test_pending_stores_are_hidden_from_public_mall(): void
    {
        $store = Store::factory()->pending()->create([
            'business_name' => 'Tienda Oculta',
            'slug' => 'tienda-oculta',
        ]);

        $this->get('/tiendas')->assertOk()->assertDontSee('Tienda Oculta');
        $this->get('/tiendas/'.$store->slug)->assertNotFound();
    }

    public function test_database_seeder_populates_jihova_mall_and_demo_portals(): void
    {
        $this->seed();

        $this->get('/')
            ->assertOk()
            ->assertSee('JIHOVA')
            ->assertSee('/images/catalog/hero-mall.jpg')
            ->assertSee('/images/catalog/desayuno.jpg')
            ->assertSee('Aurora Café & Desayunos')
            ->assertSee('StepUp Calzado');

        $customer = User::query()->where('email', 'cliente@jihova.test')->firstOrFail();
        $merchant = User::query()->where('email', 'comercio@jihova.test')->firstOrFail();
        $driver = User::query()->where('email', 'repartidor@jihova.test')->firstOrFail();
        $admin = User::query()->where('email', 'admin@jihova.test')->firstOrFail();
        $demoOrder = Order::query()->where('order_number', 'CC-DEMO-1001')->firstOrFail();

        $this->actingAs($customer)->get('/pedidos/'.$demoOrder->id)->assertOk()->assertSee('CC-DEMO-1001');
        $this->actingAs($merchant)->get('/panel-tienda')->assertOk()->assertSee('CC-DEMO-1001-T1');
        $this->actingAs($driver)->get('/panel-repartidor')->assertOk()->assertSee('CC-DEMO-1001-T2');
        $this->actingAs($admin)->get('/panel-admin')->assertOk()->assertSee('JIHOVA');
    }
}
