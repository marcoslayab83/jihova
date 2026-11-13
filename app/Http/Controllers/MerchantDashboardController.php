<?php

namespace App\Http\Controllers;

use App\Enums\SubOrderStatus;
use App\Models\BranchInventory;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\MallAdBanner;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreBranch;
use App\Models\StoreSale;
use App\Models\StoreSaleItem;
use App\Models\SubOrder;
use App\Models\User;
use App\Services\AiImageEnhancerService;
use App\Services\DeliveryDispatchService;
use App\Services\StoreSaleService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MerchantDashboardController extends Controller
{
    public function __construct(
        private readonly DeliveryDispatchService $dispatch,
        private readonly StoreSaleService $sales,
        private readonly AiImageEnhancerService $aiEnhancer,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $stores = Store::query()
            ->when(! $user->isPlatformAdmin(), fn ($q) => $q->where('owner_id', $user->id))
            ->with([
                'category',
                'branches.inventory.product.category',
                'products.inventoryRecords',
                'products.category',
            ])
            ->get();

        $branchIds = $stores->flatMap(fn (Store $s) => $s->branches->pluck('id'))->values();
        $storeIds = $stores->pluck('id');

        $subOrders = $branchIds->isEmpty()
            ? collect()
            : SubOrder::query()
                ->whereIn('branch_id', $branchIds)
                ->with([
                    'order.customer',
                    'branch.store',
                    'items',
                    'driver',
                    'trackingEvents',
                ])
                ->latest()
                ->limit(40)
                ->get();

        $activeOrders = $subOrders->filter(fn (SubOrder $so): bool => ! $so->status->isTerminal());
        $completedOrders = $subOrders->filter(fn (SubOrder $so): bool => $so->status->isTerminal());

        $metrics = [
            'pending_count' => $subOrders->whereIn('status', [SubOrderStatus::Placed, SubOrderStatus::AcceptedByStore, SubOrderStatus::Preparing])->count(),
            'ready_count' => $subOrders->where('status', SubOrderStatus::ReadyForPickup)->count(),
            'delivered_count' => $subOrders->where('status', SubOrderStatus::Delivered)->count(),
            'gross_sales' => (float) $subOrders->where('status', '!=', SubOrderStatus::Cancelled)->sum('subtotal'),
        ];

        $todaySales = $this->salesMetrics($branchIds, now()->startOfDay(), now()->endOfDay());
        $monthSales = $this->salesMetrics($branchIds, now()->startOfMonth(), now()->endOfMonth());

        $recentSales = $branchIds->isEmpty()
            ? collect()
            : StoreSale::query()
                ->whereIn('branch_id', $branchIds)
                ->with(['branch', 'items.product'])
                ->latest('sold_at')
                ->limit(15)
                ->get();

        $onlineSales = $branchIds->isEmpty()
            ? collect()
            : SubOrder::query()
                ->whereIn('branch_id', $branchIds)
                ->where('status', SubOrderStatus::Delivered)
                ->whereHas('order', fn ($query) => $query->where('payment_status', 'paid'))
                ->with(['order.customer', 'branch', 'items'])
                ->latest('delivered_at')
                ->limit(10)
                ->get();

        $recentMovements = $branchIds->isEmpty()
            ? collect()
            : InventoryMovement::query()
                ->whereIn('branch_id', $branchIds)
                ->with(['branch', 'product', 'user'])
                ->latest()
                ->limit(12)
                ->get();

        $adBanners = $storeIds->isEmpty()
            ? collect()
            : MallAdBanner::query()
                ->whereIn('store_id', $storeIds)
                ->with(['store', 'product'])
                ->latest()
                ->get();

        return view('portals.merchant', [
            'stores' => $stores,
            'activeOrders' => $activeOrders,
            'completedOrders' => $completedOrders,
            'metrics' => $metrics,
            'todaySales' => $todaySales,
            'monthSales' => $monthSales,
            'recentSales' => $recentSales,
            'onlineSales' => $onlineSales,
            'recentMovements' => $recentMovements,
            'adBanners' => $adBanners,
            'aiPresets' => $this->aiEnhancer->presets(),
            'categories' => Category::ordered()->get(),
        ]);
    }

    public function advanceSubOrder(Request $request, SubOrder $subOrder): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(SubOrderStatus::class)],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $this->dispatch->advanceByMerchant(
            $subOrder,
            SubOrderStatus::from($validated['status']),
            $request->user(),
            $validated['notes'] ?? null
        );

        return back()->with('status', "Sub-pedido {$subOrder->sub_order_code} actualizado.");
    }

    public function autoDispatch(Request $request, SubOrder $subOrder): RedirectResponse
    {
        $updated = $this->dispatch->autoAssignNearestDriver($subOrder, $request->user());

        return back()->with('status', "Repartidor {$updated->driver?->full_name} asignado automáticamente al pedido {$updated->sub_order_code}.");
    }

    public function toggleBranch(Request $request, StoreBranch $branch): RedirectResponse
    {
        abort_unless($this->dispatch->canManageBranch($request->user(), $branch), 403);

        $branch->update(['is_open' => ! $branch->is_open]);

        $stateLabel = $branch->is_open ? 'abierta' : 'cerrada';

        return back()->with('status', "Sucursal {$branch->branch_name} marcada como {$stateLabel}.");
    }

    public function updateInventory(Request $request, BranchInventory $inventory): RedirectResponse
    {
        $inventory->loadMissing('branch.store');
        abort_unless($this->dispatch->canManageBranch($request->user(), $inventory->branch), 403);

        $validated = $request->validate([
            'price' => ['required', 'numeric', 'min:0.01', 'max:99999'],
            'stock_quantity' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_available' => ['nullable', 'boolean'],
        ]);

        $data = [
            'price' => (float) $validated['price'],
            'is_available' => $request->boolean('is_available'),
        ];

        if (isset($validated['stock_quantity'])) {
            $data['stock_quantity'] = (int) $validated['stock_quantity'];
        }

        $inventory->update($data);

        return back()->with('status', 'Precio e inventario actualizados.');
    }

    public function storeInventoryMovement(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'uuid', 'exists:store_branches,id'],
            'product_id' => ['required', 'uuid', 'exists:products,id'],
            'type' => ['required', Rule::in(['entry', 'exit'])],
            'quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $inventory = BranchInventory::query()
            ->with('branch.store')
            ->where('branch_id', $validated['branch_id'])
            ->where('product_id', $validated['product_id'])
            ->firstOrFail();
        abort_unless($this->dispatch->canManageBranch($request->user(), $inventory->branch), 403);

        DB::transaction(function () use ($validated, $inventory, $request): void {
            $lockedInventory = BranchInventory::query()
                ->whereKey($inventory->id)
                ->lockForUpdate()
                ->firstOrFail();
            $stockBefore = $lockedInventory->stock_quantity;
            $quantity = (int) $validated['quantity'];
            $stockAfter = $validated['type'] === 'entry'
                ? $stockBefore + $quantity
                : $stockBefore - $quantity;

            if ($stockAfter < 0) {
                throw ValidationException::withMessages([
                    'quantity' => "No hay existencias suficientes. Stock actual: {$stockBefore}.",
                ]);
            }

            $lockedInventory->update(['stock_quantity' => $stockAfter]);

            InventoryMovement::create([
                'branch_id' => $lockedInventory->branch_id,
                'product_id' => $lockedInventory->product_id,
                'user_id' => $request->user()->id,
                'type' => $validated['type'],
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reason' => $validated['reason'],
            ]);
        });

        return back()->with('status', 'Movimiento de inventario registrado.');
    }

    public function registerSale(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'uuid', 'exists:store_branches,id'],
            'items' => ['required', 'array', 'min:1', 'max:30'],
            'items.*' => ['required', 'array:product_id,quantity'],
            'items.*.product_id' => ['required', 'uuid', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'channel' => ['required', Rule::in(['in_store', 'whatsapp', 'phone'])],
            'payment_method' => ['required', Rule::in([
                'cash_usd',
                'cash_ves',
                'pago_movil',
                'pos_card',
                'transfer',
                'binance_usdt',
            ])],
            'customer_name' => ['nullable', 'string', 'max:150'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $branch = StoreBranch::query()->with('store')->findOrFail($validated['branch_id']);
        abort_unless($this->dispatch->canManageBranch($request->user(), $branch), 403);

        $sale = $this->sales->register(
            $branch,
            $request->user(),
            $validated['items'],
            $validated['channel'],
            $validated['payment_method'],
            $validated['customer_name'] ?? null,
            $validated['customer_phone'] ?? null,
            $validated['payment_reference'] ?? null,
            $validated['notes'] ?? null,
        );

        return redirect()->route('merchant.dashboard')->with('status', "Venta {$sale->sale_number} registrada con éxito por $".number_format((float) $sale->total_amount, 2).'.');
    }

    public function storeSale(Request $request): RedirectResponse
    {
        return $this->registerSale($request);
    }

    public function showSale(Request $request, StoreSale $sale): JsonResponse|View
    {
        $sale->loadMissing(['store', 'branch', 'items.product', 'user']);
        abort_unless($this->dispatch->canManageBranch($request->user(), $sale->branch), 403);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'sale' => $sale,
            ]);
        }

        return view('portals.merchant.sale-receipt', ['sale' => $sale]);
    }

    public function updateProductPricing(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'cost_price' => ['required', 'numeric', 'min:0.10', 'max:99999'],
            'profit_margin_percent' => ['required', 'numeric', 'min:0', 'max:1000'],
            'discount_percent' => ['required', 'numeric', 'min:0', 'max:99'],
        ]);

        $product->loadMissing('store');
        abort_unless($request->user()->isPlatformAdmin() || $product->store->owner_id === $request->user()->id, 403);

        $costPrice = (float) $validated['cost_price'];
        $marginPercent = (float) $validated['profit_margin_percent'];
        $discountPercent = (float) $validated['discount_percent'];
        $regularPrice = round($costPrice * (1 + ($marginPercent / 100)), 2);
        $sellingPrice = round($regularPrice * (1 - ($discountPercent / 100)), 2);

        if ($sellingPrice < 0.01) {
            throw ValidationException::withMessages([
                'discount_percent' => 'El precio final debe ser mayor que cero.',
            ]);
        }

        DB::transaction(function () use ($product, $costPrice, $marginPercent, $discountPercent, $regularPrice, $sellingPrice): void {
            $lockedProduct = Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();
            $lockedProduct->update([
                'cost_price' => $costPrice,
                'profit_margin_percent' => $marginPercent,
                'discount_percent' => $discountPercent,
                'base_price' => $regularPrice,
            ]);
            $lockedProduct->inventoryRecords()->update(['price' => $sellingPrice]);
        });

        return back()->with('status', "Precio actualizado para {$product->name} en todas sus sucursales.");
    }

    public function storeProduct(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_id' => ['required', 'uuid', 'exists:stores,id'],
            'branch_id' => ['required', 'uuid', 'exists:store_branches,id'],
            'category_id' => ['required', 'uuid', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string', 'max:1000'],
            'cost_price' => ['nullable', 'numeric', 'min:0.10', 'max:99999'],
            'price' => ['nullable', 'numeric', 'min:0.10', 'max:99999'],
            'profit_margin_percent' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:99'],
            'stock_quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'badge' => ['nullable', 'string', 'max:50'],
            'prep_note' => ['nullable', 'string', 'max:100'],
            'image_file' => ['nullable', 'image', 'max:5120'],
            'image_camera' => ['nullable', 'image', 'max:5120'],
            'image_base64' => ['nullable', 'string'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'enhance_with_ai' => ['nullable', 'boolean'],
            'ai_preset' => ['nullable', 'string', 'in:neon_studio,promo_banner,cyber_glow,clean_hd'],
        ]);

        $store = Store::query()->findOrFail($validated['store_id']);
        $branch = StoreBranch::query()->where('store_id', $store->id)->findOrFail($validated['branch_id']);
        abort_unless($this->dispatch->canManageBranch($request->user(), $branch), 403);

        $category = Category::query()->findOrFail($validated['category_id']);
        $imageMap = [
            'desayunos-cafe' => '/images/catalog/desayuno.jpg',
            'calzado-moda' => '/images/catalog/tenis.jpg',
            'tecnologia' => '/images/catalog/tecnologia.jpg',
            'farmacia-salud' => '/images/catalog/farmacia.jpg',
            'supermercado-hogar' => '/images/catalog/supermercado.jpg',
        ];

        // Resolución de precio y costos (compatible con price directo o cost_price + margen)
        if (isset($validated['cost_price']) && is_numeric($validated['cost_price'])) {
            $costPrice = (float) $validated['cost_price'];
            $marginPercent = (float) ($validated['profit_margin_percent'] ?? 0);
            $discountPercent = (float) ($validated['discount_percent'] ?? 0);
            $regularPrice = round($costPrice * (1 + ($marginPercent / 100)), 2);
            $sellingPrice = round($regularPrice * (1 - ($discountPercent / 100)), 2);
        } else {
            $sellingPrice = (float) ($validated['price'] ?? 10.00);
            $regularPrice = $sellingPrice;
            $costPrice = round($sellingPrice * 0.70, 2);
            $marginPercent = round((($regularPrice - $costPrice) / $costPrice) * 100, 2);
            $discountPercent = 0.0;
        }

        if ($sellingPrice < 0.01) {
            throw ValidationException::withMessages([
                'price' => 'El precio final de venta debe ser mayor a cero.',
            ]);
        }

        // Carga o asignación de fotografía real (archivo subido, cámara de teléfono, base64 o URL)
        $imageUrl = $imageMap[$category->slug] ?? '/images/catalog/moda.jpg';
        $file = $request->file('image_file') ?? $request->file('image_camera');

        if ($file) {
            $uploadDir = public_path('uploads/products');
            if (! File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true, true);
            }
            $filename = 'prod_'.Str::lower(Str::random(12)).'.'.$file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $imageUrl = '/uploads/products/'.$filename;
        } elseif ($request->filled('image_base64') && str_starts_with((string) $request->input('image_base64'), 'data:image')) {
            $uploadDir = public_path('uploads/products');
            if (! File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true, true);
            }
            $base64Parts = explode(',', (string) $request->input('image_base64'));
            $decoded = base64_decode($base64Parts[1] ?? '');
            if ($decoded !== false && strlen($decoded) > 0) {
                $filename = 'cam_'.Str::lower(Str::random(12)).'.jpg';
                file_put_contents($uploadDir.'/'.$filename, $decoded);
                $imageUrl = '/uploads/products/'.$filename;
            }
        } elseif (! empty($validated['image_url'])) {
            $imageUrl = $validated['image_url'];
        }

        // Opción de edición/mejora automática con IA al crear
        $aiEnhancedImageUrl = null;
        if ($request->boolean('enhance_with_ai') || $request->boolean('auto_enhance_ai')) {
            $preset = $validated['ai_preset'] ?? 'neon_studio';
            $aiEnhancedImageUrl = $this->aiEnhancer->enhance($imageUrl, $preset, [
                'product_name' => $validated['name'],
                'badge' => $validated['badge'] ?? 'Nuevo',
                'price' => (string) $sellingPrice,
                'store_name' => $store->business_name,
            ]);
        }

        DB::transaction(function () use (
            $validated,
            $store,
            $branch,
            $category,
            $costPrice,
            $marginPercent,
            $discountPercent,
            $regularPrice,
            $sellingPrice,
            $imageUrl,
            $aiEnhancedImageUrl,
            $request
        ): void {
            $product = Product::create([
                'store_id' => $store->id,
                'category_id' => $category->id,
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']).'-'.Str::lower(Str::random(4)),
                'description' => $validated['description'],
                'sku' => 'SKU-'.strtoupper(Str::random(6)),
                'base_price' => $regularPrice,
                'cost_price' => $costPrice,
                'profit_margin_percent' => $marginPercent,
                'discount_percent' => $discountPercent,
                'image_url' => $imageUrl,
                'ai_enhanced_image_url' => $aiEnhancedImageUrl,
                'badge' => $validated['badge'] ?? 'Nuevo',
                'prep_note' => $validated['prep_note'] ?? 'Disponible hoy',
                'is_active' => true,
            ]);

            BranchInventory::create([
                'branch_id' => $branch->id,
                'product_id' => $product->id,
                'price' => $sellingPrice,
                'stock_quantity' => (int) $validated['stock_quantity'],
                'is_available' => true,
            ]);

            InventoryMovement::create([
                'branch_id' => $branch->id,
                'product_id' => $product->id,
                'user_id' => $request->user()->id,
                'type' => 'entry',
                'quantity' => (int) $validated['stock_quantity'],
                'stock_before' => 0,
                'stock_after' => (int) $validated['stock_quantity'],
                'reason' => 'Existencia inicial del artículo',
            ]);
        });

        return redirect()->route('merchant.dashboard')->with('status', "Producto '{$validated['name']}' publicado con éxito en {$branch->branch_name}.");
    }

    public function updateProduct(Request $request, Product $product): RedirectResponse
    {
        $product->loadMissing('store');
        abort_unless($request->user()->isPlatformAdmin() || $product->store->owner_id === $request->user()->id, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string', 'max:1000'],
            'badge' => ['nullable', 'string', 'max:50'],
            'prep_note' => ['nullable', 'string', 'max:100'],
            'image_file' => ['nullable', 'image', 'max:5120'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data = [
            'name' => $validated['name'],
            'description' => $validated['description'],
            'badge' => $validated['badge'] ?? null,
            'prep_note' => $validated['prep_note'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $uploadDir = public_path('uploads/products');
            if (! File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true, true);
            }
            $filename = 'prod_'.Str::lower(Str::random(12)).'.'.$file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $data['image_url'] = '/uploads/products/'.$filename;
        } elseif (! empty($validated['image_url'])) {
            $data['image_url'] = $validated['image_url'];
        }

        $product->update($data);

        return back()->with('status', "Producto '{$product->name}' actualizado correctamente.");
    }

    public function destroyProduct(Request $request, Product $product): RedirectResponse
    {
        $product->loadMissing('store');
        abort_unless($request->user()->isPlatformAdmin() || $product->store->owner_id === $request->user()->id, 403);

        $productName = $product->name;
        $product->delete();

        return back()->with('status', "Producto '{$productName}' eliminado del catálogo.");
    }

    public function aiEnhanceProduct(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $product->loadMissing('store');
        abort_unless($request->user()->isPlatformAdmin() || $product->store->owner_id === $request->user()->id, 403);

        $validated = $request->validate([
            'preset' => ['required', 'string', Rule::in(array_keys($this->aiEnhancer->presets()))],
            'badge' => ['nullable', 'string', 'max:50'],
            'apply_as_main' => ['nullable', 'boolean'],
        ]);

        $enhancedUrl = $this->aiEnhancer->enhanceProduct($product, $validated['preset'], [
            'badge' => $validated['badge'] ?? null,
        ]);

        if ($request->boolean('apply_as_main')) {
            $product->update(['image_url' => $enhancedUrl]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'image_url' => $enhancedUrl,
                'message' => 'Imagen mejorada con IA correctamente.',
            ]);
        }

        return back()->with('status', "Imagen de '{$product->name}' procesada con el preset de IA '{$validated['preset']}'.");
    }

    public function aiGenerateBanner(Request $request): RedirectResponse|JsonResponse
    {
        if ($request->has('product_id') && ! $request->has('store_id')) {
            $prod = Product::query()->find($request->input('product_id'));
            if ($prod) {
                $request->merge(['store_id' => $prod->store_id]);
                if (! $request->has('headline') && ! $request->has('title')) {
                    $request->merge(['headline' => $prod->name]);
                }
            }
        }

        if ($request->has('title') && ! $request->has('headline')) {
            $request->merge(['headline' => $request->input('title')]);
        }
        if ($request->has('subtitle') && ! $request->has('subline')) {
            $request->merge(['subline' => $request->input('subtitle')]);
        }
        if ($request->has('cta_text') && ! $request->has('tag')) {
            $request->merge(['tag' => $request->input('cta_text')]);
        }
        if ($request->input('placement') === 'hero_carousel') {
            $request->merge(['placement' => 'hero_spotlight']);
        } elseif ($request->input('placement') === 'vitrina_grid') {
            $request->merge(['placement' => 'vitrina_home']);
        }
        if (! $request->has('publish_ad')) {
            $request->merge(['publish_ad' => true]);
        }

        $validated = $request->validate([
            'store_id' => ['required', 'uuid', 'exists:stores,id'],
            'product_id' => ['nullable', 'uuid', 'exists:products,id'],
            'headline' => ['required', 'string', 'max:120'],
            'subline' => ['nullable', 'string', 'max:200'],
            'badge' => ['nullable', 'string', 'max:50'],
            'tag' => ['nullable', 'string', 'max:50'],
            'placement' => ['nullable', 'string', 'in:hero_spotlight,vitrina_home,both'],
            'publish_ad' => ['nullable', 'boolean'],
        ]);

        $store = Store::query()->findOrFail($validated['store_id']);
        abort_unless($request->user()->isPlatformAdmin() || $store->owner_id === $request->user()->id, 403);

        $product = ! empty($validated['product_id'])
            ? Product::query()->where('store_id', $store->id)->findOrFail($validated['product_id'])
            : null;

        $bannerUrl = $this->aiEnhancer->generateAdBanner($store, $product, $validated);

        $adBanner = null;
        if ($request->boolean('publish_ad')) {
            $adBanner = MallAdBanner::create([
                'store_id' => $store->id,
                'product_id' => $product?->id,
                'headline' => $validated['headline'],
                'subline' => $validated['subline'] ?? null,
                'badge' => $validated['badge'] ?? '✦ OFERTA EXCLUSIVA',
                'tag' => $validated['tag'] ?? ($product ? 'Desde $'.number_format((float) $product->base_price, 2) : 'Tienda Verificada'),
                'image_url' => $bannerUrl,
                'target_url' => $product ? route('mall.products.show', [$store, $product]) : route('mall.stores.show', $store),
                'placement' => $validated['placement'] ?? 'hero_spotlight',
                'is_active' => true,
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'banner_url' => $bannerUrl,
                'ad_banner' => $adBanner,
                'message' => 'Banner publicitario 16:9 generado con IA con éxito.',
            ]);
        }

        return redirect()->route('merchant.dashboard')->with('status', 'Banner generado con IA y publicado en la página principal.');
    }

    public function storeAdBanner(Request $request): RedirectResponse
    {
        if ($request->has('title') && ! $request->has('headline')) {
            $request->merge(['headline' => $request->input('title')]);
        }
        if ($request->has('subtitle') && ! $request->has('subline')) {
            $request->merge(['subline' => $request->input('subtitle')]);
        }
        if ($request->has('cta_text') && ! $request->has('tag')) {
            $request->merge(['tag' => $request->input('cta_text')]);
        }
        if ($request->input('placement') === 'hero_carousel') {
            $request->merge(['placement' => 'hero_spotlight']);
        } elseif ($request->input('placement') === 'vitrina_grid') {
            $request->merge(['placement' => 'vitrina_home']);
        }

        $validated = $request->validate([
            'store_id' => ['required', 'uuid', 'exists:stores,id'],
            'product_id' => ['nullable', 'uuid', 'exists:products,id'],
            'headline' => ['required', 'string', 'max:150'],
            'subline' => ['nullable', 'string', 'max:255'],
            'badge' => ['nullable', 'string', 'max:60'],
            'tag' => ['nullable', 'string', 'max:60'],
            'target_url' => ['nullable', 'string', 'max:500'],
            'placement' => ['required', 'string', 'in:hero_spotlight,vitrina_home,both'],
            'image_file' => ['nullable', 'image', 'max:5120'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'generate_ai_banner' => ['nullable', 'boolean'],
        ]);

        $store = Store::query()->findOrFail($validated['store_id']);
        abort_unless($request->user()->isPlatformAdmin() || $store->owner_id === $request->user()->id, 403);

        $product = ! empty($validated['product_id'])
            ? Product::query()->where('store_id', $store->id)->findOrFail($validated['product_id'])
            : null;

        $imageUrl = $product?->image_url ?? ($store->banner_url ?? '/images/catalog/hero-mall.jpg');

        if ($request->boolean('generate_ai_banner')) {
            $imageUrl = $this->aiEnhancer->generateAdBanner($store, $product, $validated);
        } elseif ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $uploadDir = public_path('uploads/ads');
            if (! File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true, true);
            }
            $filename = 'ad_'.Str::lower(Str::random(12)).'.'.$file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $imageUrl = '/uploads/ads/'.$filename;
        } elseif (! empty($validated['image_url'])) {
            $imageUrl = $validated['image_url'];
        }

        $targetUrl = ! empty($validated['target_url'])
            ? $validated['target_url']
            : ($product ? route('mall.products.show', [$store, $product]) : route('mall.stores.show', $store));

        MallAdBanner::create([
            'store_id' => $store->id,
            'product_id' => $product?->id,
            'headline' => $validated['headline'],
            'subline' => $validated['subline'] ?? null,
            'badge' => $validated['badge'] ?? '✦ OFERTA EXCLUSIVA',
            'tag' => $validated['tag'] ?? null,
            'image_url' => $imageUrl,
            'target_url' => $targetUrl,
            'placement' => $validated['placement'],
            'is_active' => true,
        ]);

        return redirect()->route('merchant.dashboard')->with('status', 'Anuncio publicitario publicado en la página principal de JIHOVA.');
    }

    public function toggleAdBanner(Request $request, MallAdBanner $adBanner): RedirectResponse
    {
        $adBanner->loadMissing('store');
        abort_unless($request->user()->isPlatformAdmin() || $adBanner->store->owner_id === $request->user()->id, 403);

        $adBanner->update(['is_active' => ! $adBanner->is_active]);

        $state = $adBanner->is_active ? 'activado' : 'pausado';

        return back()->with('status', "Anuncio '{$adBanner->headline}' {$state}.");
    }

    public function destroyAdBanner(Request $request, MallAdBanner $adBanner): RedirectResponse
    {
        $adBanner->loadMissing('store');
        abort_unless($request->user()->isPlatformAdmin() || $adBanner->store->owner_id === $request->user()->id, 403);

        $adBanner->delete();

        return back()->with('status', 'Anuncio publicitario eliminado.');
    }

    /**
     * @return array{gross_sales: float, gross_profit: float, uncosted_items: int, sales_count: int}
     */
    private function salesMetrics(Collection $branchIds, Carbon $start, Carbon $end): array
    {
        $storeSales = StoreSale::query()
            ->whereIn('branch_id', $branchIds)
            ->whereBetween('sold_at', [$start, $end]);
        $storeSalesRevenue = (float) (clone $storeSales)->sum('total_amount');
        $storeSaleItems = StoreSaleItem::query()
            ->join('store_sales', 'store_sales.id', '=', 'store_sale_items.store_sale_id')
            ->whereIn('store_sales.branch_id', $branchIds)
            ->whereBetween('store_sales.sold_at', [$start, $end]);
        $storeSaleCosts = (float) (clone $storeSaleItems)
            ->whereNotNull('store_sale_items.unit_cost')
            ->sum(DB::raw('store_sale_items.unit_cost * store_sale_items.quantity'));
        $uncostedStoreItems = (clone $storeSaleItems)
            ->whereNull('store_sale_items.unit_cost')
            ->count();

        $onlineOrders = SubOrder::query()
            ->whereIn('branch_id', $branchIds)
            ->where('status', SubOrderStatus::Delivered)
            ->whereBetween('delivered_at', [$start, $end])
            ->whereHas('order', fn ($query) => $query->where('payment_status', 'paid'));
        $onlineRevenue = (float) (clone $onlineOrders)->sum('subtotal');
        $onlineCommission = (float) (clone $onlineOrders)->sum('commission_amount');
        $onlineItems = OrderItem::query()
            ->join('sub_orders', 'sub_orders.id', '=', 'order_items.sub_order_id')
            ->join('orders', 'orders.id', '=', 'sub_orders.order_id')
            ->whereIn('sub_orders.branch_id', $branchIds)
            ->where('sub_orders.status', SubOrderStatus::Delivered->value)
            ->whereBetween('sub_orders.delivered_at', [$start, $end])
            ->where('orders.payment_status', 'paid');
        $onlineCosts = (float) (clone $onlineItems)
            ->whereNotNull('order_items.unit_cost')
            ->sum(DB::raw('order_items.unit_cost * order_items.quantity'));
        $uncostedOnlineItems = (clone $onlineItems)
            ->whereNull('order_items.unit_cost')
            ->count();

        return [
            'gross_sales' => $storeSalesRevenue + $onlineRevenue,
            'gross_profit' => $storeSalesRevenue + $onlineRevenue - $storeSaleCosts - $onlineCosts - $onlineCommission,
            'uncosted_items' => $uncostedStoreItems + $uncostedOnlineItems,
            'sales_count' => (clone $storeSales)->count() + (clone $onlineOrders)->count(),
        ];
    }
}
