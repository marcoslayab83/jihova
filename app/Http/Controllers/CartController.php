<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cart) {}

    public function index(Request $request): View|JsonResponse
    {
        $location = $this->cart->activeLocation($request->user()?->id);
        $summary = $this->cart->summary($location['latitude'], $location['longitude']);

        if ($request->expectsJson()) {
            return response()->json([
                'cart' => $this->formatJsonSummary($summary),
            ]);
        }

        return view('mall.cart', [
            'cart' => $summary,
            'activeLocation' => $location,
        ]);
    }

    public function add(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'uuid'],
            'product_id' => ['required', 'uuid'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $summary = $this->cart->addItem(
            $validated['branch_id'],
            $validated['product_id'],
            (int) ($validated['quantity'] ?? 1)
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Producto agregado al carrito.',
                'cart' => $this->formatJsonSummary($summary),
            ]);
        }

        return back()->with('status', 'Producto agregado a tu carrito del centro comercial.');
    }

    public function update(Request $request, string $branchId, string $productId): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:50'],
        ]);

        $summary = $this->cart->updateQuantity($branchId, $productId, (int) $validated['quantity']);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Carrito actualizado.',
                'cart' => $this->formatJsonSummary($summary),
            ]);
        }

        return back()->with('status', 'Cantidad actualizada en el carrito.');
    }

    public function remove(Request $request, string $branchId, string $productId): RedirectResponse|JsonResponse
    {
        $summary = $this->cart->removeItem($branchId, $productId);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Artículo eliminado.',
                'cart' => $this->formatJsonSummary($summary),
            ]);
        }

        return back()->with('status', 'Artículo eliminado del carrito.');
    }

    public function clear(Request $request): RedirectResponse|JsonResponse
    {
        $this->cart->clear();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Carrito vaciado.',
                'cart' => $this->formatJsonSummary($this->cart->summary()),
            ]);
        }

        return back()->with('status', 'Has vaciado tu carrito.');
    }

    /**
     * @param  array<string, mixed>  $summary
     * @return array<string, mixed>
     */
    private function formatJsonSummary(array $summary): array
    {
        return [
            'items_count' => $summary['items_count'],
            'stores_count' => $summary['stores_count'],
            'total_items_amount' => $summary['total_items_amount'],
            'total_delivery_fee' => $summary['total_delivery_fee'],
            'platform_fee' => $summary['platform_fee'],
            'grand_total' => $summary['grand_total'],
            'warnings' => $summary['warnings'],
            'store_groups' => $summary['store_groups']->map(fn (array $group): array => [
                'branch_id' => $group['branch']->id,
                'branch_name' => $group['branch']->branch_name,
                'store_name' => $group['store']->business_name,
                'store_slug' => $group['store']->slug,
                'distance_km' => $group['distance_km'],
                'eta_minutes' => $group['eta_minutes'],
                'delivery_fee' => $group['delivery_fee'],
                'within_radius' => $group['within_radius'],
                'subtotal' => $group['subtotal'],
                'items' => array_map(fn (array $item): array => [
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'image_url' => $item['product']->image_url,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ], $group['items']),
            ])->values(),
        ];
    }
}
