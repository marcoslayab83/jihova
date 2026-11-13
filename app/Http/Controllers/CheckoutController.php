<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\CustomerAddress;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\GeoLocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly CheckoutService $checkout,
        private readonly GeoLocationService $geo,
    ) {}

    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $addresses = CustomerAddress::query()
            ->where('user_id', $user->id)
            ->orderByDesc('is_default')
            ->get();

        $activeLocation = $this->cart->activeLocation($user->id);
        $selectedAddress = $addresses->firstWhere('id', $request->string('address_id')->toString())
            ?? $addresses->firstWhere('id', $activeLocation['address_id'])
            ?? $addresses->first();

        $lat = $selectedAddress ? (float) $selectedAddress->latitude : $activeLocation['latitude'];
        $lng = $selectedAddress ? (float) $selectedAddress->longitude : $activeLocation['longitude'];

        $cartSummary = $this->cart->summary($lat, $lng);

        if ($cartSummary['items_count'] === 0) {
            return redirect()->route('mall.explore')
                ->with('status', 'Tu carrito está vacío. Explora el centro comercial para agregar productos.');
        }

        return view('mall.checkout', [
            'cart' => $cartSummary,
            'addresses' => $addresses,
            'selectedAddress' => $selectedAddress,
            'presetZones' => $this->geo->presetZones(),
            'activeLocation' => $activeLocation,
        ]);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        if (! empty($validated['address_id'])) {
            $address = CustomerAddress::query()
                ->where('user_id', $user->id)
                ->findOrFail($validated['address_id']);
        } else {
            $newAddressData = $validated['new_address'];
            $hasAnyAddress = CustomerAddress::query()->where('user_id', $user->id)->exists();

            $address = CustomerAddress::create([
                'user_id' => $user->id,
                'title' => $newAddressData['title'],
                'street_address' => $newAddressData['street_address'],
                'reference' => $newAddressData['reference'] ?? null,
                'latitude' => (float) $newAddressData['latitude'],
                'longitude' => (float) $newAddressData['longitude'],
                'is_default' => ! $hasAnyAddress,
            ]);
        }

        $this->cart->setLocation(
            $address->title.' · '.$address->street_address,
            (float) $address->latitude,
            (float) $address->longitude,
            $address->id
        );

        $order = $this->checkout->placeFromCart(
            $user,
            $address,
            $validated['payment_method'],
            $validated['customer_notes'] ?? null
        );

        return redirect()->route('orders.show', $order)
            ->with('status', "¡Pago confirmado! Tu orden {$order->order_number} ya está siendo gestionada por nuestras tiendas y repartidores.");
    }
}
