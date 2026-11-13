<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderApiController extends Controller
{
    public function __construct(private readonly CheckoutService $checkout) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'address_id' => [
                'required',
                'uuid',
                Rule::exists('customer_addresses', 'id')->where('user_id', $request->user()->id),
            ],
            'payment_method' => ['required', 'string', Rule::in(['card', 'mobile_pay', 'wallet', 'cash_on_delivery'])],
            'customer_notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.branch_id' => ['required', 'uuid'],
            'items.*.product_id' => ['required', 'uuid'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $address = CustomerAddress::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($validated['address_id']);

        $order = $this->checkout->placeOrder(
            $request->user(),
            $address,
            $validated['items'],
            $validated['payment_method'],
            $validated['customer_notes'] ?? null
        );

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Order $order): OrderResource
    {
        $user = $request->user();
        if ($order->customer_id !== $user->id && ! $user->isPlatformAdmin()) {
            abort(403, 'No tienes acceso a esta orden.');
        }

        $order->load([
            'subOrders.branch.store',
            'subOrders.driver',
            'subOrders.items',
            'subOrders.trackingEvents',
        ]);

        return new OrderResource($order);
    }
}
