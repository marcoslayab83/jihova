<?php

namespace Tests\Feature;

use App\Models\BranchInventory;
use App\Models\Category;
use App\Models\MallAdBanner;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreBranch;
use App\Models\StoreSale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MerchantSuiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_merchant_can_upload_product_with_image_file_and_ai_auto_enhancement(): void
    {
        $merchant = User::factory()->merchantAdmin()->create();
        $store = Store::factory()->create(['owner_id' => $merchant->id]);
        $branch = StoreBranch::factory()->create(['store_id' => $store->id]);
        $category = Category::factory()->create();

        $image = UploadedFile::fake()->image('tenis-cyber.jpg', 600, 600);

        $response = $this->actingAs($merchant)->post(route('merchant.products.store'), [
            'store_id' => $store->id,
            'branch_id' => $branch->id,
            'category_id' => $category->id,
            'name' => 'Zapatillas Hyper-Neon Runner',
            'cost_price' => 20.00,
            'profit_margin_percent' => 50,
            'discount_percent' => 10,
            'stock_quantity' => 25,
            'description' => 'Zapatillas futuristas edición especial JIHOVA',
            'image_file' => $image,
            'auto_enhance_ai' => '1',
            'ai_preset' => 'neon_studio',
        ]);

        $response->assertRedirect(route('merchant.dashboard'));

        $product = Product::where('name', 'Zapatillas Hyper-Neon Runner')->first();
        $this->assertNotNull($product);
        $this->assertNotNull($product->image_url);
        $this->assertNotNull($product->ai_enhanced_image_url);
        $this->assertStringContainsString('neon-studio', $product->ai_enhanced_image_url);

        // Branch inventory record created
        $inventory = BranchInventory::where('branch_id', $branch->id)->where('product_id', $product->id)->first();
        $this->assertNotNull($inventory);
        $this->assertSame(25, $inventory->stock_quantity);
    }

    public function test_merchant_can_upload_product_from_phone_camera_capture(): void
    {
        $merchant = User::factory()->merchantAdmin()->create();
        $store = Store::factory()->create(['owner_id' => $merchant->id]);
        $branch = StoreBranch::factory()->create(['store_id' => $store->id]);
        $category = Category::factory()->create();

        $cameraPhoto = UploadedFile::fake()->image('camera_snap.jpg', 800, 600);

        $response = $this->actingAs($merchant)->post(route('merchant.products.store'), [
            'store_id' => $store->id,
            'branch_id' => $branch->id,
            'category_id' => $category->id,
            'name' => 'Vestido Casual Floral Fotocámara',
            'cost_price' => 18.00,
            'profit_margin_percent' => 40,
            'stock_quantity' => 12,
            'description' => 'Fotografía tomada directamente desde el teléfono del comerciante',
            'image_camera' => $cameraPhoto,
            'auto_enhance_ai' => '1',
            'ai_preset' => 'clean_hd',
        ]);

        $response->assertRedirect(route('merchant.dashboard'));

        $product = Product::where('name', 'Vestido Casual Floral Fotocámara')->first();
        $this->assertNotNull($product);
        $this->assertNotNull($product->image_url);
        $this->assertStringContainsString('prod_', $product->image_url);
        $this->assertNotNull($product->ai_enhanced_image_url);
    }

    public function test_merchant_can_apply_ai_preset_to_product(): void
    {
        $merchant = User::factory()->merchantAdmin()->create();
        $store = Store::factory()->create(['owner_id' => $merchant->id]);
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'image_url' => '/images/catalog/tenis.jpg',
        ]);

        $response = $this->actingAs($merchant)->post(route('merchant.products.ai-enhance', $product), [
            'preset' => 'clean_hd',
        ]);

        $response->assertRedirect();
        $product->refresh();
        $this->assertNotNull($product->ai_enhanced_image_url);
        $this->assertStringContainsString('clean', $product->ai_enhanced_image_url);
    }

    public function test_merchant_can_publish_ad_banner_and_tracking_counts_clicks(): void
    {
        $merchant = User::factory()->merchantAdmin()->create();
        $store = Store::factory()->create(['owner_id' => $merchant->id]);
        $product = Product::factory()->create(['store_id' => $store->id]);

        $bannerImage = UploadedFile::fake()->image('banner-16-9.jpg', 1200, 675);

        $response = $this->actingAs($merchant)->post(route('merchant.ads.store'), [
            'store_id' => $store->id,
            'product_id' => $product->id,
            'title' => 'Gran Liquidación Neón 2026',
            'subtitle' => 'Descuentos de hasta 40% en calzado y accesorios',
            'cta_text' => 'Aprovechar Oferta',
            'placement' => 'hero_carousel',
            'banner_file' => $bannerImage,
        ]);

        $response->assertRedirect(route('merchant.dashboard'));

        $ad = MallAdBanner::where('headline', 'Gran Liquidación Neón 2026')->first();
        $this->assertNotNull($ad);
        $this->assertSame('hero_spotlight', $ad->placement);
        $this->assertSame(0, $ad->clicks_count);
        $this->assertTrue($ad->is_active);

        // Now test click tracking
        $clickResponse = $this->get(route('mall.ads.click', $ad));
        $clickResponse->assertRedirect();

        $ad->refresh();
        $this->assertSame(1, $ad->clicks_count);
    }

    public function test_merchant_can_generate_ai_banner_from_product(): void
    {
        $merchant = User::factory()->merchantAdmin()->create();
        $store = Store::factory()->create(['owner_id' => $merchant->id]);
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'name' => 'Audífonos Bluetooth ANC Pro',
            'base_price' => 45.00,
            'image_url' => '/images/catalog/tecnologia.jpg',
        ]);

        $response = $this->actingAs($merchant)->post(route('merchant.ai.generate-banner'), [
            'product_id' => $product->id,
            'placement' => 'hero_carousel',
        ]);

        $response->assertRedirect(route('merchant.dashboard'));

        $ad = MallAdBanner::where('product_id', $product->id)->first();
        $this->assertNotNull($ad);
        $this->assertSame('hero_spotlight', $ad->placement);
        $this->assertNotNull($ad->image_url);
    }

    public function test_merchant_can_toggle_and_delete_ad_banner(): void
    {
        $merchant = User::factory()->merchantAdmin()->create();
        $store = Store::factory()->create(['owner_id' => $merchant->id]);
        $ad = MallAdBanner::factory()->create([
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        // Toggle to paused
        $this->actingAs($merchant)
            ->patch(route('merchant.ads.toggle', $ad))
            ->assertRedirect();

        $this->assertFalse($ad->fresh()->is_active);

        // Toggle back to active
        $this->actingAs($merchant)
            ->patch(route('merchant.ads.toggle', $ad))
            ->assertRedirect();

        $this->assertTrue($ad->fresh()->is_active);

        // Delete
        $this->actingAs($merchant)
            ->delete(route('merchant.ads.destroy', $ad))
            ->assertRedirect();

        $this->assertDatabaseMissing('mall_ad_banners', ['id' => $ad->id]);
    }

    public function test_merchant_can_register_point_of_sale_and_inventory_deducts_atomically(): void
    {
        $merchant = User::factory()->merchantAdmin()->create();
        $store = Store::factory()->create(['owner_id' => $merchant->id]);
        $branch = StoreBranch::factory()->create(['store_id' => $store->id]);
        $productA = Product::factory()->create(['store_id' => $store->id]);
        $productB = Product::factory()->create(['store_id' => $store->id]);

        $invA = BranchInventory::factory()->create([
            'branch_id' => $branch->id,
            'product_id' => $productA->id,
            'stock_quantity' => 10,
            'price' => 15.00,
            'is_available' => true,
        ]);

        $invB = BranchInventory::factory()->create([
            'branch_id' => $branch->id,
            'product_id' => $productB->id,
            'stock_quantity' => 8,
            'price' => 25.00,
            'is_available' => true,
        ]);

        $response = $this->actingAs($merchant)->post(route('merchant.sales.store'), [
            'branch_id' => $branch->id,
            'channel' => 'in_store',
            'payment_method' => 'cash_usd',
            'customer_name' => 'Carlos Mendoza',
            'payment_reference' => 'BILL-9081',
            'items' => [
                ['product_id' => $productA->id, 'quantity' => 3],
                ['product_id' => $productB->id, 'quantity' => 2],
            ],
        ]);

        $response->assertRedirect(route('merchant.dashboard'));

        // Check stock deductions
        $this->assertSame(7, $invA->fresh()->stock_quantity); // 10 - 3
        $this->assertSame(6, $invB->fresh()->stock_quantity); // 8 - 2

        // Check sale recorded
        $sale = StoreSale::where('branch_id', $branch->id)->first();
        $this->assertNotNull($sale);
        $this->assertSame('in_store', $sale->channel);
        $this->assertSame('cash_usd', $sale->payment_method);
        // Total: 3 * 15 + 2 * 25 = 45 + 50 = 95.00
        $this->assertEquals(95.00, (float) $sale->total_amount);
        $this->assertCount(2, $sale->items);

        // Receipt can be viewed
        $receiptResponse = $this->actingAs($merchant)->get(route('merchant.sales.show', $sale));
        $receiptResponse->assertOk();
    }

    public function test_merchant_can_create_new_store(): void
    {
        $merchant = User::factory()->merchantAdmin()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($merchant)->post(route('merchant.stores.store'), [
            'business_name' => 'Pastelería Francesa Le Bon',
            'category_id' => $category->id,
            'tagline' => 'Postres y café de alta pastelería',
            'phone' => '+58 412 5556677',
            'address' => 'Centro San Ignacio, Nivel Jardín, Local 12',
            'delivery_radius_km' => 15.0,
            'avg_prep_time_minutes' => 25,
            'description' => 'Especialistas en croissants, tartas y repostería artesanal.',
        ]);

        $response->assertRedirect(route('merchant.dashboard'));

        $store = Store::where('business_name', 'Pastelería Francesa Le Bon')->first();
        $this->assertNotNull($store);
        $this->assertSame($merchant->id, $store->owner_id);
        $this->assertSame($category->id, $store->category_id);
        $this->assertSame(\App\Enums\StoreStatus::Active, $store->status);

        // Check primary branch created
        $branch = StoreBranch::where('store_id', $store->id)->first();
        $this->assertNotNull($branch);
        $this->assertSame('Centro San Ignacio, Nivel Jardín, Local 12', $branch->address);
        $this->assertTrue($branch->is_open);
    }
}
