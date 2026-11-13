<x-layouts.mall title="Panel de Repartidor · JIHOVA Delivery">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <!-- Header del Repartidor -->
        <div class="relative overflow-hidden bg-gradient-to-r from-[#1A1344] via-[#121728] to-[#0B2B3F] border border-[#00F0FF]/30 text-white rounded-3xl p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-6 shadow-2xl">
            <div class="space-y-1">
                <span class="px-2.5 py-1 rounded-full bg-[#00F0FF]/15 border border-[#00F0FF]/40 text-[#00F0FF] text-xs font-bold">
                    JIHOVA Delivery · Flota Propia
                </span>
                <h1 class="font-display text-2xl sm:text-3xl font-extrabold">{{ auth()->user()->full_name }}</h1>
                <p class="text-sm text-slate-300">
                    {{ $profile->vehicle_type?->label() ?? 'Moto' }}
                    @if ($profile->license_plate) · Placa {{ $profile->license_plate }} @endif
                    · Entregas completadas: <strong class="text-[#00F0FF]">{{ $profile->completed_deliveries }}</strong>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <form action="{{ route('driver.shift.toggle') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 rounded-2xl text-sm font-bold cursor-pointer transition {{ $profile->is_active ? 'bg-[#00F0FF] hover:bg-cyan-300 text-[#0B0E17] shadow-lg shadow-[#00F0FF]/25' : 'bg-[#0B0E17] border border-white/15 hover:border-[#00F0FF] text-white' }}">
                        {{ $profile->is_active ? '● En línea (Pausar turno)' : '○ Fuera de turno (Activar)' }}
                    </button>
                </form>

                <form action="{{ route('driver.location.update') }}" method="POST" class="flex items-center gap-2 bg-[#0B0E17]/80 px-3 py-1.5 rounded-2xl border border-white/15">
                    @csrf
                    <select id="driver-zone-select" class="bg-[#0B0E17] text-xs text-white focus:outline-none">
                        @foreach ($presetZones as $zone)
                            <option value="{{ $zone['latitude'] }}|{{ $zone['longitude'] }}">{{ $zone['label'] }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="latitude" id="driver-lat-input" value="{{ $profile->current_latitude ?? $presetZones[0]['latitude'] }}">
                    <input type="hidden" name="longitude" id="driver-lng-input" value="{{ $profile->current_longitude ?? $presetZones[0]['longitude'] }}">
                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-[#FF3366] hover:bg-[#ff4d7a] text-white text-xs font-bold cursor-pointer transition">
                        Actualizar GPS
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Mis Entregas Activas -->
            <section class="lg:col-span-6 space-y-4">
                <h2 class="font-display text-xl font-extrabold text-white">Mis Entregas en Curso ({{ $myActiveDeliveries->count() }})</h2>
                @forelse ($myActiveDeliveries as $subOrder)
                    @php
                        $snapshot = $subOrder->order->delivery_snapshot ?? [];
                    @endphp
                    <div class="bg-[#121728] rounded-3xl border-2 border-[#00F0FF]/55 p-5 shadow-[0_12px_36px_-14px_rgba(0,240,255,0.3)] space-y-4">
                        <div class="flex items-center justify-between gap-2">
                            <div>
                                <span class="font-mono text-xs font-bold text-[#00F0FF]">{{ $subOrder->sub_order_code }}</span>
                                <h3 class="font-display font-extrabold text-white">{{ $subOrder->branch->store->business_name }} → {{ $snapshot['title'] ?? 'Cliente' }}</h3>
                            </div>
                            <x-status-badge :status="$subOrder->status" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs bg-[#0B0E17]/75 p-3.5 rounded-2xl border border-white/10">
                            <div>
                                <p class="font-bold text-[#00F0FF]">1. Retiro en Tienda</p>
                                <p class="text-white font-semibold">{{ $subOrder->branch->branch_name }}</p>
                                <p class="text-slate-400">{{ $subOrder->branch->address }}</p>
                                <p class="mt-1 font-mono font-bold text-violet-300">PIN Retiro: {{ $subOrder->pickup_pin }}</p>
                            </div>
                            <div>
                                <p class="font-bold text-[#FF3366]">2. Entrega en Domicilio</p>
                                <p class="text-white font-semibold">{{ $subOrder->order->customer->full_name }}</p>
                                <p class="text-slate-400">{{ $snapshot['street_address'] ?? '' }}</p>
                                <p class="mt-1 font-mono font-bold text-amber-300">PIN Cliente: {{ $subOrder->delivery_pin }}</p>
                            </div>
                        </div>

                        <div class="text-xs text-slate-300 space-y-1">
                            <p><strong class="text-white">Artículos:</strong> {{ $subOrder->items->map(fn ($i) => $i->quantity.'x '.$i->product_name)->join(', ') }}</p>
                            @if ($subOrder->order->customer_notes)
                                <p class="text-amber-200 bg-amber-500/15 px-2.5 py-1.5 rounded-xl border border-amber-400/35">
                                    <strong>Nota del cliente:</strong> {{ $subOrder->order->customer_notes }}
                                </p>
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-2">
                            @if ($subOrder->status === \App\Enums\SubOrderStatus::DriverAssigned)
                                <form action="{{ route('driver.sub-orders.advance', $subOrder) }}" method="POST" class="w-full">
                                    @csrf
                                    <input type="hidden" name="status" value="out_for_delivery">
                                    <button type="submit" class="w-full py-3 rounded-2xl bg-[#6C28FE] hover:bg-[#7d42ff] text-white text-sm font-bold shadow-lg shadow-[#6C28FE]/30 cursor-pointer transition">
                                        📦 Confirmar retiro en tienda e iniciar ruta
                                    </button>
                                </form>
                            @elseif ($subOrder->status === \App\Enums\SubOrderStatus::OutForDelivery)
                                <form action="{{ route('driver.sub-orders.advance', $subOrder) }}" method="POST" class="w-full">
                                    @csrf
                                    <input type="hidden" name="status" value="delivered">
                                    <button type="submit" class="w-full py-3 rounded-2xl bg-[#00F0FF] hover:bg-cyan-300 text-[#0B0E17] text-sm font-extrabold shadow-lg shadow-[#00F0FF]/25 cursor-pointer transition">
                                        ✅ Confirmar entrega en casa del cliente
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="bg-[#121728] rounded-3xl border border-white/10 p-8 text-center text-sm text-slate-400">
                        No tienes entregas asignadas en curso. Toma un envío disponible de la bolsa.
                    </div>
                @endforelse
            </section>

            <!-- Bolsa de Envíos Disponibles -->
            <section class="lg:col-span-6 space-y-4">
                <h2 class="font-display text-xl font-extrabold text-white">Bolsa de Envíos Listos ({{ $availablePool->count() }})</h2>
                @forelse ($availablePool as $item)
                    @php
                        $subOrder = $item['sub_order'];
                        $snapshot = $subOrder->order->delivery_snapshot ?? [];
                    @endphp
                    <div class="bg-[#121728] rounded-3xl border border-white/10 p-5 shadow-xl space-y-3 hover:border-[#6C28FE]/55 transition">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-full bg-fuchsia-500/15 border border-fuchsia-400/35 text-fuchsia-300 text-[11px] font-bold">Listo en tienda</span>
                                    <span class="font-mono text-[11px] text-slate-400">{{ $subOrder->sub_order_code }}</span>
                                </div>
                                <h3 class="font-bold text-white mt-1">{{ $subOrder->branch->store->business_name }} · {{ $subOrder->branch->branch_name }}</h3>
                                <p class="text-xs text-slate-400">Destino: {{ $snapshot['title'] ?? 'Cliente' }} ({{ $snapshot['street_address'] ?? '' }})</p>
                            </div>
                            <span class="text-base font-extrabold text-emerald-300">+${{ number_format((float) $subOrder->delivery_fee, 2) }}</span>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-white/10 text-xs text-slate-300">
                            <span>
                                @if ($item['pickup_distance_km'] !== null)
                                    A {{ $item['pickup_distance_km'] }} km de ti ·
                                @endif
                                Ruta {{ $item['trip_distance_km'] }} km
                            </span>
                            <form action="{{ route('driver.sub-orders.claim', $subOrder) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 rounded-xl bg-[#FF3366] hover:bg-[#ff4d7a] text-white text-xs font-bold shadow-md shadow-[#FF3366]/25 cursor-pointer transition">
                                    Tomar envío →
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="bg-[#121728] rounded-3xl border border-white/10 p-8 text-center text-sm text-slate-400">
                        No hay paquetes pendientes en espera de repartidor en este momento.
                    </div>
                @endforelse

                @if ($myCompletedDeliveries->isNotEmpty())
                    <div class="bg-[#121728] rounded-3xl border border-white/10 p-5 space-y-3">
                        <h3 class="font-display text-sm font-bold text-white">Últimas entregas completadas</h3>
                        <ul class="divide-y divide-white/10 text-xs">
                            @foreach ($myCompletedDeliveries as $done)
                                <li class="py-2 flex justify-between">
                                    <span class="text-slate-300 font-medium">{{ $done->sub_order_code }} · {{ $done->branch->store->business_name }}</span>
                                    <span class="text-[#00F0FF] font-bold">${{ number_format((float) $done->delivery_fee, 2) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </section>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('driver-zone-select')?.addEventListener('change', (e) => {
            const [lat, lng] = e.target.value.split('|');
            document.getElementById('driver-lat-input').value = lat;
            document.getElementById('driver-lng-input').value = lng;
        });
    </script>
    @endpush
</x-layouts.mall>
