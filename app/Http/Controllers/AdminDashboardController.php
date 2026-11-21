<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Enums\StoreStatus;
use App\Enums\SubOrderStatus;
use App\Models\DriverProfile;
use App\Models\Order;
use App\Models\Store;
use App\Models\SubOrder;
use App\Models\User;
use App\Services\DeliveryDispatchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct(private readonly DeliveryDispatchService $dispatch) {}

    public function index(): View
    {
        $stores = Store::query()
            ->with(['owner', 'category', 'branches'])
            ->orderBy('business_name')
            ->get();

        $subOrders = SubOrder::query()
            ->with(['order.customer', 'branch.store', 'driver'])
            ->latest()
            ->limit(30)
            ->get();

        $drivers = DriverProfile::query()
            ->with('user')
            ->orderByDesc('is_active')
            ->get();

        $metrics = [
            'gmv' => (float) Order::query()->where('payment_status', PaymentStatus::Paid->value)->sum('grand_total'),
            'commissions' => (float) SubOrder::query()->where('status', '!=', SubOrderStatus::Cancelled->value)->sum('commission_amount'),
            'active_stores' => $stores->where('status', StoreStatus::Active)->count(),
            'pending_stores' => $stores->where('status', StoreStatus::Pending)->count(),
            'active_sub_orders' => SubOrder::query()->whereNotIn('status', [SubOrderStatus::Delivered->value, SubOrderStatus::Cancelled->value])->count(),
            'online_drivers' => $drivers->where('is_active', true)->count(),
        ];

        return view('portals.admin', [
            'stores' => $stores,
            'subOrders' => $subOrders,
            'drivers' => $drivers,
            'metrics' => $metrics,
        ]);
    }

    public function updateStoreStatus(Request $request, Store $store): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(StoreStatus::class)],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'bcv_rate' => ['nullable', 'numeric', 'min:0.01'],
        ]);

        $updateData = [
            'status' => StoreStatus::from($validated['status']),
        ];

        if (isset($validated['commission_rate'])) {
            $updateData['commission_rate'] = (float) $validated['commission_rate'];
        }

        if (isset($validated['bcv_rate'])) {
            $updateData['bcv_rate'] = (float) $validated['bcv_rate'];
        }

        $store->update($updateData);

        return back()->with('status', "Tienda {$store->business_name} actualizada (Estado: {$store->status->label()}).");
    }

    public function assignDriver(Request $request, SubOrder $subOrder): RedirectResponse
    {
        $validated = $request->validate([
            'driver_id' => ['required', 'uuid', 'exists:users,id'],
        ]);

        $driver = User::query()->findOrFail($validated['driver_id']);
        $this->dispatch->claimByDriver($subOrder, $driver);

        return back()->with('status', "Repartidor {$driver->full_name} asignado a {$subOrder->sub_order_code}.");
    }
}
