<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\BranchInventory;
use App\Models\Category;
use App\Models\Store;
use App\Models\StoreBranch;
use App\Models\SubOrder;
use App\Services\BcvExchangeService;
use App\Services\CashShiftService;
use App\Services\PosSaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosController extends Controller
{
    public function __construct(
        protected PosSaleService $posSaleService,
        protected CashShiftService $shiftService,
        protected BcvExchangeService $exchangeService
    ) {}

    /**
     * Muestra la interfaz principal del Punto de Venta (POS) Omnicanal
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // 1. Obtener tiendas del comerciante (Dueño o empleado asignado)
        $stores = Store::query()
            ->when(! $user->isPlatformAdmin(), function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                    ->orWhereHas('storeUsers', fn ($su) => $su->where('user_id', $user->id)->where('is_active', true));
            })
            ->with(['branches.cashRegisters'])
            ->get();

        if ($stores->isEmpty()) {
            abort(403, 'No tiene ninguna tienda asignada para operar el Punto de Venta.');
        }

        // 2. Determinar la sucursal activa
        $requestedBranchId = $request->query('branch_id');
        $allBranches = $stores->flatMap->branches;

        /** @var StoreBranch|null $activeBranch */
        $activeBranch = $requestedBranchId
            ? $allBranches->firstWhere('id', $requestedBranchId)
            : $allBranches->first();

        if (! $activeBranch) {
            abort(404, 'La sucursal seleccionada no existe o no tiene permisos de acceso.');
        }

        $activeStore = $activeBranch->store;

        // 3. Obtener o crear la caja registradora principal y el turno activo
        $cashRegister = $this->shiftService->getOrCreateDefaultRegister($activeBranch);
        $activeShift = $cashRegister->activeShift;

        // 4. Catálogo de productos para la cuadrícula táctil y lector de código de barras
        $inventoryRecords = BranchInventory::query()
            ->with(['product.category'])
            ->where('branch_id', $activeBranch->id)
            ->where('is_available', true)
            ->whereHas('product', fn ($q) => $q->where('is_active', true))
            ->get();

        $rate = $this->exchangeService->getStoreRate($activeStore);

        // Formatear catálogo optimizado para el motor POS reactivo (cliente JS / LocalStorage)
        $catalog = $inventoryRecords->map(function (BranchInventory $inv) use ($rate) {
            $product = $inv->product;
            $unitPriceUsd = (float) ($inv->price ?? $product->base_price);
            $unitPriceVes = $this->exchangeService->usdToVes($unitPriceUsd, $rate);

            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku ?? '',
                'category_id' => $product->category_id,
                'category_name' => $product->category?->name ?? 'General',
                'image_url' => $product->image_url ?? $product->image_path ?? null,
                'stock' => (int) $inv->stock_quantity,
                'price_usd' => $unitPriceUsd,
                'price_ves' => $unitPriceVes,
            ];
        });

        // 5. Categorías únicas con productos en stock
        $categories = Category::query()
            ->whereIn('id', $catalog->pluck('category_id')->unique()->filter())
            ->orderBy('name')
            ->get();

        // 6. Resumen de turno actual (si está abierto)
        $shiftSummary = $activeShift ? $this->shiftService->getShiftSummary($activeShift) : null;

        return view('portals.merchant.pos.index', [
            'stores' => $stores,
            'activeStore' => $activeStore,
            'activeBranch' => $activeBranch,
            'cashRegister' => $cashRegister,
            'activeShift' => $activeShift,
            'shiftSummary' => $shiftSummary,
            'catalog' => $catalog,
            'categories' => $categories,
            'bcvRate' => $rate,
            'isSpecialTaxpayer' => (bool) $activeStore->is_special_taxpayer,
            'user' => $user,
        ]);
    }

    /**
     * Procesa y completa una venta en mostrador (POS)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'uuid', 'exists:store_branches,id'],
            'cash_shift_id' => ['required', 'uuid', 'exists:cash_shifts,id'],
            'customer_doc' => ['nullable', 'string', 'max:30'],
            'customer_name' => ['nullable', 'string', 'max:120'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'uuid', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'payments' => ['required', 'array', 'min:1'],
            'payments.*.method' => ['required', 'string', 'in:efectivo_usd,efectivo_ves,pago_movil,punto_venta,zelle,jihova_wallet'],
            'payments.*.currency' => ['required', 'string', 'in:USD,VES'],
            'payments.*.amount' => ['required', 'numeric', 'min:0.01'],
            'payments.*.reference_number' => ['nullable', 'string', 'max:50'],
            'payments.*.notes' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();
        $branch = StoreBranch::with('store')->findOrFail($validated['branch_id']);
        $shift = $branch->cashRegisters()->firstOrFail()->shifts()->findOrFail($validated['cash_shift_id']);

        $customerData = [
            'doc' => $validated['customer_doc'] ?? null,
            'name' => $validated['customer_name'] ?? null,
            'phone' => $validated['customer_phone'] ?? null,
        ];

        $receiptData = $this->posSaleService->processSale(
            $branch,
            $user,
            $shift,
            $customerData,
            $validated['items'],
            $validated['payments'],
            $validated['notes'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => '¡Venta completada con éxito!',
            'receipt' => $receiptData,
        ]);
    }

    /**
     * Obtiene los pedidos online entrantes en tiempo real para la sucursal (Polling reactivo)
     */
    public function liveOrders(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'uuid', 'exists:store_branches,id'],
        ]);

        $branch = StoreBranch::with('store')->findOrFail($validated['branch_id']);
        $orders = $this->posSaleService->getLiveOnlineOrders($branch);

        return response()->json([
            'success' => true,
            'branch_id' => $branch->id,
            'count' => count($orders),
            'orders' => $orders,
        ]);
    }

    /**
     * Actualiza el estado de un pedido online entrante desde el POS (ej. Aceptar, Preparar)
     */
    public function updateOrderStatus(Request $request, SubOrder $subOrder): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:accepted_by_store,preparing,ready_for_pickup,delivered,cancelled'],
        ]);

        $updated = $this->posSaleService->updateOnlineOrderStatus($subOrder, $validated['status']);

        return response()->json([
            'success' => true,
            'message' => 'Estado del pedido actualizado.',
            'order' => [
                'sub_order_id' => $updated->id,
                'status' => $updated->status->value,
                'status_label' => $updated->status->label(),
            ],
        ]);
    }
}
