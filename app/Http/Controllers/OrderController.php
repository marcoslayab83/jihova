<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->where('customer_id', $request->user()->id)
            ->with([
                'subOrders.branch.store',
                'subOrders.items',
                'subOrders.driver',
            ])
            ->latest()
            ->paginate(10);

        return view('orders.index', [
            'orders' => $orders,
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        $user = $request->user();
        if ($order->customer_id !== $user->id && ! $user->isPlatformAdmin()) {
            abort(403, 'No puedes consultar órdenes de otro cliente.');
        }

        $order->load([
            'customer',
            'address',
            'subOrders.branch.store.category',
            'subOrders.items.product',
            'subOrders.driver.driverProfile',
            'subOrders.trackingEvents',
        ]);

        return view('orders.show', [
            'order' => $order,
        ]);
    }

    public function statusJson(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();
        if ($order->customer_id !== $user->id && ! $user->isPlatformAdmin()) {
            abort(403);
        }

        $order->load([
            'subOrders.branch.store',
            'subOrders.driver.driverProfile',
            'subOrders.trackingEvents',
        ]);

        return response()->json([
            'order_number' => $order->order_number,
            'payment_status' => $order->payment_status->value,
            'payment_status_label' => $order->payment_status->label(),
            'sub_orders' => $order->subOrders->map(fn ($subOrder): array => [
                'id' => $subOrder->id,
                'code' => $subOrder->sub_order_code,
                'store_name' => $subOrder->branch->store->business_name,
                'branch_name' => $subOrder->branch->branch_name,
                'status' => $subOrder->status->value,
                'status_label' => $subOrder->status->label(),
                'step_index' => $subOrder->status->stepIndex(),
                'estimated_delivery_at' => $subOrder->estimated_delivery_at?->format('H:i'),
                'delivered_at' => $subOrder->delivered_at?->format('H:i'),
                'driver' => $subOrder->driver ? [
                    'name' => $subOrder->driver->full_name,
                    'phone' => $subOrder->driver->phone,
                    'vehicle' => $subOrder->driver->driverProfile?->vehicle_type?->label(),
                    'license_plate' => $subOrder->driver->driverProfile?->license_plate,
                    'latitude' => $subOrder->driver->driverProfile?->current_latitude,
                    'longitude' => $subOrder->driver->driverProfile?->current_longitude,
                ] : null,
                'events' => $subOrder->trackingEvents->map(fn ($event): array => [
                    'status' => $event->status,
                    'timestamp' => $event->event_timestamp->format('H:i'),
                    'notes' => $event->notes,
                ])->values(),
            ])->values(),
        ]);
    }
}
