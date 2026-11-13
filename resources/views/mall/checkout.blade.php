<x-layouts.mall title="Checkout y Pago · JIHOVA">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-[#00F0FF]">Pago Unificado y Envío</p>
            <h1 class="font-display text-2xl sm:text-3xl font-extrabold text-white">Finalizar Compra</h1>
            <p class="text-sm text-slate-400">Confirmamos el stock de todas tus tiendas al instante y despachamos tu delivery.</p>
        </div>

        <form action="{{ route('checkout.store') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            @csrf

            <!-- Columna izquierda: Dirección y Método de pago -->
            <div class="lg:col-span-7 space-y-6">
                <!-- Paso 1: Dirección de entrega -->
                <section class="bg-[#121728] rounded-3xl border border-white/10 p-6 space-y-4 shadow-xl">
                    <div class="flex items-center justify-between">
                        <h2 class="font-display text-lg font-extrabold text-white">1. ¿Dónde te lo llevamos?</h2>
                        <a href="{{ route('addresses.index') }}" class="text-xs font-bold text-[#00F0FF] hover:text-white transition">Gestionar direcciones</a>
                    </div>

                    @if ($addresses->isNotEmpty())
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach ($addresses as $address)
                                <label class="block p-4 rounded-2xl border cursor-pointer transition {{ old('address_id', $selectedAddress?->id) === $address->id ? 'border-[#00F0FF] bg-[#00F0FF]/10' : 'border-white/15 bg-[#0B0E17]/60 hover:border-white/30' }}">
                                    <div class="flex items-start gap-2.5">
                                        <input type="radio" name="address_id" value="{{ $address->id }}" @checked(old('address_id', $selectedAddress?->id) === $address->id) class="mt-1 accent-[#FF3366]">
                                        <div class="text-xs space-y-1">
                                            <p class="font-bold text-white text-sm">{{ $address->title }}</p>
                                            <p class="text-slate-300">{{ $address->street_address }}</p>
                                            @if ($address->reference)
                                                <p class="text-slate-400">Ref: {{ $address->reference }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @endif

                    <!-- Opción de dirección rápida nueva -->
                    <div class="pt-3 border-t border-white/10 space-y-3">
                        <label class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-300 cursor-pointer">
                            <input type="radio" name="address_id" value="" @checked($addresses->isEmpty() || old('address_id') === '') class="accent-[#FF3366]">
                            <span>Usar / guardar una dirección nueva ahora</span>
                        </label>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-[#0B0E17]/75 p-4 rounded-2xl border border-white/10">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Alias (Ej. Casa)</label>
                                <input type="text" name="new_address[title]" value="{{ old('new_address.title', 'Casa') }}" class="w-full px-3 py-2 rounded-xl border border-white/15 bg-[#121728] text-white text-sm focus:outline-none focus:border-[#00F0FF]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Zona rápida (GPS)</label>
                                <select id="checkout-zone-preset" class="w-full px-3 py-2 rounded-xl border border-white/15 bg-[#121728] text-white text-sm focus:outline-none focus:border-[#00F0FF]">
                                    @foreach ($presetZones as $zone)
                                        <option value="{{ $zone['latitude'] }}|{{ $zone['longitude'] }}">{{ $zone['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Dirección completa</label>
                                <input type="text" name="new_address[street_address]" value="{{ old('new_address.street_address') }}" placeholder="Avenida, edificio, casa, piso..." class="w-full px-3 py-2 rounded-xl border border-white/15 bg-[#121728] text-white placeholder:text-slate-500 text-sm focus:outline-none focus:border-[#00F0FF]">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Referencia para el repartidor</label>
                                <input type="text" name="new_address[reference]" value="{{ old('new_address.reference') }}" placeholder="Ej. Portón negro frente a la plaza" class="w-full px-3 py-2 rounded-xl border border-white/15 bg-[#121728] text-white placeholder:text-slate-500 text-sm focus:outline-none focus:border-[#00F0FF]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Latitud</label>
                                <input type="number" step="0.000001" id="new-address-lat" name="new_address[latitude]" value="{{ old('new_address.latitude', $activeLocation['latitude']) }}" class="w-full px-3 py-2 rounded-xl border border-white/15 bg-[#121728] text-white text-sm focus:outline-none focus:border-[#00F0FF]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Longitud</label>
                                <input type="number" step="0.000001" id="new-address-lng" name="new_address[longitude]" value="{{ old('new_address.longitude', $activeLocation['longitude']) }}" class="w-full px-3 py-2 rounded-xl border border-white/15 bg-[#121728] text-white text-sm focus:outline-none focus:border-[#00F0FF]">
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Paso 2: Método de Pago -->
                <section class="bg-[#121728] rounded-3xl border border-white/10 p-6 space-y-4 shadow-xl">
                    <h2 class="font-display text-lg font-extrabold text-white">2. Método de pago</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="payment-method-selector">
                        @foreach ([
                            'card' => ['label' => 'Tarjeta Crédito / Débito', 'desc' => 'Aprobación instantánea'],
                            'mobile_pay' => ['label' => 'Pago Móvil / Transferencia', 'desc' => 'Validación automática'],
                            'wallet' => ['label' => 'Billetera JIHOVA', 'desc' => 'Saldo disponible al instante'],
                            'cash_on_delivery' => ['label' => 'Efectivo contra entrega', 'desc' => 'Pagas al repartidor al recibir'],
                        ] as $methodKey => $methodMeta)
                            <label class="p-3.5 rounded-2xl border border-white/15 bg-[#0B0E17]/60 hover:border-[#00F0FF]/55 cursor-pointer flex items-start gap-2.5 transition">
                                <input type="radio" name="payment_method" value="{{ $methodKey }}" @checked(old('payment_method', 'card') === $methodKey) class="mt-1 accent-[#FF3366]">
                                <div>
                                    <span class="block text-sm font-bold text-white">{{ $methodMeta['label'] }}</span>
                                    <span class="block text-xs text-slate-400">{{ $methodMeta['desc'] }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <!-- Campos de tarjeta simulada -->
                    <div id="card-fields" class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-[#0B0E17]/75 p-4 rounded-2xl border border-white/10">
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Titular de la tarjeta</label>
                            <input type="text" name="card_holder" value="{{ old('card_holder', auth()->user()->full_name) }}" class="w-full px-3 py-2 rounded-xl border border-white/15 bg-[#121728] text-white text-sm focus:outline-none focus:border-[#00F0FF]">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Número de tarjeta (demo)</label>
                            <input type="text" name="card_number" value="{{ old('card_number', '4242 4242 4242 4242') }}" class="w-full px-3 py-2 rounded-xl border border-white/15 bg-[#121728] text-white text-sm font-mono focus:outline-none focus:border-[#00F0FF]">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Venc.</label>
                                <input type="text" name="card_expiry" value="{{ old('card_expiry', '12/29') }}" class="w-full px-3 py-2 rounded-xl border border-white/15 bg-[#121728] text-white text-sm font-mono focus:outline-none focus:border-[#00F0FF]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">CVV</label>
                                <input type="text" name="card_cvv" value="{{ old('card_cvv', '842') }}" class="w-full px-3 py-2 rounded-xl border border-white/15 bg-[#121728] text-white text-sm font-mono focus:outline-none focus:border-[#00F0FF]">
                            </div>
                        </div>
                    </div>

                    <div id="mobile-pay-fields" class="hidden bg-[#0B0E17]/75 p-4 rounded-2xl border border-white/10">
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Teléfono asociado al Pago Móvil</label>
                        <input type="text" name="mobile_phone" value="{{ old('mobile_phone', auth()->user()->phone) }}" class="w-full px-3 py-2 rounded-xl border border-white/15 bg-[#121728] text-white text-sm focus:outline-none focus:border-[#00F0FF]">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Notas para las tiendas o repartidor (opcional)</label>
                        <textarea name="customer_notes" rows="2" placeholder="Ej. Sin cebolla en el desayuno, zapatos talla 41..." class="w-full px-3.5 py-2 rounded-xl border border-white/15 bg-[#0B0E17] text-white placeholder:text-slate-500 text-sm focus:outline-none focus:border-[#00F0FF]">{{ old('customer_notes') }}</textarea>
                    </div>
                </section>
            </div>

            <!-- Columna derecha: Desglose de Sub-Órdenes -->
            <aside class="lg:col-span-5 bg-[#121728] rounded-3xl border border-white/10 p-6 space-y-5 sticky top-24 shadow-xl">
                <h2 class="font-display text-lg font-extrabold text-white">Desglose por tienda ({{ $cart['stores_count'] }})</h2>

                <div class="space-y-3">
                    @foreach ($cart['store_groups'] as $group)
                        <div class="p-3.5 rounded-2xl bg-[#0B0E17]/75 border border-white/10 space-y-2 text-xs">
                            <div class="flex justify-between items-center font-bold text-white">
                                <span class="text-[#00F0FF]">🏪 {{ $group['store']->business_name }}</span>
                                <span class="text-slate-300">~{{ $group['eta_minutes'] }} min</span>
                            </div>
                            @foreach ($group['items'] as $item)
                                <div class="flex justify-between text-slate-300">
                                    <span>{{ $item['quantity'] }}x {{ $item['product']->name }}</span>
                                    <span class="font-semibold text-white">${{ number_format($item['total_price'], 2) }}</span>
                                </div>
                            @endforeach
                            <div class="pt-1.5 border-t border-white/10 flex justify-between text-slate-400">
                                <span>Delivery tienda</span>
                                <span>${{ number_format($group['delivery_fee'], 2) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="space-y-2 text-sm pt-2 border-t border-white/10">
                    <div class="flex justify-between text-slate-300">
                        <span>Subtotal artículos</span>
                        <span>${{ number_format($cart['total_items_amount'], 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-300">
                        <span>Total delivery</span>
                        <span>${{ number_format($cart['total_delivery_fee'], 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-300">
                        <span>Tarifa plataforma</span>
                        <span>${{ number_format($cart['platform_fee'], 2) }}</span>
                    </div>
                    <div class="flex justify-between text-xl font-extrabold text-white pt-2 border-t border-white/10">
                        <span>Total a pagar</span>
                        <span class="text-[#00F0FF]">${{ number_format($cart['grand_total'], 2) }}</span>
                    </div>
                </div>

                <button type="submit" class="w-full py-4 rounded-2xl bg-[#FF3366] hover:bg-[#ff4d7a] text-white font-bold text-base shadow-lg shadow-[#FF3366]/30 transition cursor-pointer">
                    Confirmar y Pagar ${{ number_format($cart['grand_total'], 2) }}
                </button>
            </aside>
        </form>
    </div>

    @push('scripts')
    <script>
        (function () {
            const radios = document.querySelectorAll('input[name="payment_method"]');
            const cardFields = document.getElementById('card-fields');
            const mobileFields = document.getElementById('mobile-pay-fields');
            function toggleMethod() {
                const selected = document.querySelector('input[name="payment_method"]:checked')?.value;
                cardFields?.classList.toggle('hidden', selected !== 'card');
                mobileFields?.classList.toggle('hidden', selected !== 'mobile_pay');
            }
            radios.forEach((r) => r.addEventListener('change', toggleMethod));
            toggleMethod();

            const preset = document.getElementById('checkout-zone-preset');
            preset?.addEventListener('change', (e) => {
                const [lat, lng] = e.target.value.split('|');
                document.getElementById('new-address-lat').value = lat;
                document.getElementById('new-address-lng').value = lng;
            });
        })();
    </script>
    @endpush
</x-layouts.mall>
