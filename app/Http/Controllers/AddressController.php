<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAddressRequest;
use App\Models\CustomerAddress;
use App\Services\CartService;
use App\Services\GeoLocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AddressController extends Controller
{
    public function __construct(
        private readonly GeoLocationService $geo,
        private readonly CartService $cart,
    ) {}

    public function index(Request $request): View
    {
        $addresses = CustomerAddress::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        return view('account.addresses', [
            'addresses' => $addresses,
            'presetZones' => $this->geo->presetZones(),
        ]);
    }

    public function store(StoreAddressRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        $setDefault = (bool) ($validated['is_default'] ?? false);

        if ($setDefault) {
            CustomerAddress::query()->where('user_id', $user->id)->update(['is_default' => false]);
        }

        $hasAny = CustomerAddress::query()->where('user_id', $user->id)->exists();

        $address = CustomerAddress::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'street_address' => $validated['street_address'],
            'reference' => $validated['reference'] ?? null,
            'latitude' => (float) $validated['latitude'],
            'longitude' => (float) $validated['longitude'],
            'is_default' => $setDefault || ! $hasAny,
        ]);

        if ($address->is_default) {
            $this->cart->setLocation(
                $address->title.' · '.$address->street_address,
                (float) $address->latitude,
                (float) $address->longitude,
                $address->id
            );
        }

        return back()->with('status', 'Dirección guardada correctamente.');
    }

    public function setDefault(Request $request, CustomerAddress $address): RedirectResponse
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }

        CustomerAddress::query()->where('user_id', $request->user()->id)->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        $this->cart->setLocation(
            $address->title.' · '.$address->street_address,
            (float) $address->latitude,
            (float) $address->longitude,
            $address->id
        );

        return back()->with('status', "'{$address->title}' establecida como dirección predeterminada.");
    }

    public function destroy(Request $request, CustomerAddress $address): RedirectResponse
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($address->orders()->exists()) {
            return back()->withErrors([
                'address' => 'No puedes eliminar una dirección vinculada a pedidos existentes.',
            ]);
        }

        $address->delete();

        return back()->with('status', 'Dirección eliminada.');
    }
}
