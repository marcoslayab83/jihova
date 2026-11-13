<?php

namespace Tests\Feature;

use App\Enums\StoreStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndRolePortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_middleware_enforces_portal_boundaries(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer);
        $this->get('/panel-tienda')->assertForbidden();
        $this->get('/panel-repartidor')->assertForbidden();
        $this->get('/panel-admin')->assertForbidden();
    }

    public function test_merchant_registration_creates_pending_store_and_admin_can_approve_it(): void
    {
        Category::factory()->create(['name' => 'Calzado', 'slug' => 'calzado']);

        $this->post('/registro', [
            'account_type' => UserRole::MerchantAdmin->value,
            'full_name' => 'Lucas Gómez',
            'email' => 'lucas@tienda.test',
            'phone' => '+58 412 999 8877',
            'store_name' => 'Zapatería Relámpago',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertRedirect(route('merchant.dashboard'));

        $store = Store::query()->where('business_name', 'Zapatería Relámpago')->firstOrFail();
        $this->assertSame(StoreStatus::Pending, $store->status);
        $this->assertCount(1, $store->branches);

        $admin = User::factory()->platformAdmin()->create();
        $this->actingAs($admin)
            ->get('/panel-admin')
            ->assertOk()
            ->assertSee('Zapatería Relámpago');

        $this->actingAs($admin)
            ->patch("/panel-admin/tiendas/{$store->id}/estado", ['status' => StoreStatus::Active->value])
            ->assertRedirect();

        $this->assertSame(StoreStatus::Active, $store->fresh()->status);
    }

    public function test_demo_login_switches_roles_seamlessly_in_testing_environment(): void
    {
        $this->seed();

        $this->post('/demo-login', ['email' => 'cliente@jihova.test'])
            ->assertRedirect(route('mall.home'));

        $this->post('/demo-login', ['email' => 'comercio@jihova.test'])
            ->assertRedirect(route('merchant.dashboard'));

        $this->post('/demo-login', ['email' => 'repartidor@jihova.test'])
            ->assertRedirect(route('driver.dashboard'));

        $this->post('/demo-login', ['email' => 'admin@jihova.test'])
            ->assertRedirect(route('admin.dashboard'));
    }
}
