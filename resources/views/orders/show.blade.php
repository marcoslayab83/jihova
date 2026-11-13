<x-layouts.mall :title="'Pedido '.$order->order_number.' · Seguimiento Delivery'">
    @php
        $snapshot = $order->delivery_snapshot ?? [];
        $destLat = (float) ($snapshot['latitude'] ?? 10.4880);
        $destLng = (float) ($snapshot['longitude'] ?? -66.8792);
        $hasActiveSubOrders = $order->subOrders->contains(fn ($so) => ! $so->status->isTerminal());
    @endphp

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <!-- Encabezado de Orden -->
        <div class="bg-[#121728] rounded-3xl border border-white/10 p-6 shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#00F0FF]">Seguimiento en Vivo</span>
                    <x-status-badge :status="$order->payment_status" />
                    @if ($hasActiveSubOrders)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/15 text-emerald-300 text-xs font-semibold border border-emerald-400/35">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                            Actualización automática activa
                        </span>
                    @endif
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white font-mono">{{ $order->order_number }}</h1>
                <p class="text-sm text-slate-300">
                    Entregar en <strong class="text-white">{{ $snapshot['title'] ?? 'Dirección' }}</strong>: {{ $snapshot['street_address'] ?? '' }}
                </p>
            </div>

            <div class="bg-[#0B0E17]/80 rounded-2xl p-4 border border-white/10 text-sm space-y-1 min-w-[230px]">
                <div class="flex justify-between text-slate-400">
                    <span>Método:</span>
                    <span class="font-semibold text-white">{{ $order->paymentMethodLabel() }}</span>
                </div>
                <div class="flex justify-between text-slate-400">
                    <span>Ref. pago:</span>
                    <span class="font-mono text-xs text-slate-200">{{ $order->payment_reference }}</span>
                </div>
                <div class="flex justify-between text-base font-extrabold text-white pt-1 border-t border-white/10">
                    <span>Total:</span>
                    <span class="text-[#00F0FF]">${{ number_format((float) $order->grand_total, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Tarjetas de Seguimiento por Tienda (Sub-Órdenes) -->
        <div class="space-y-6" id="sub-orders-tracking-container">
            @foreach ($order->subOrders as $subOrder)
                @php
                    $step = $subOrder->status->stepIndex();
                    $steps = [
                        1 => 'Recibido',
                        2 => 'Aceptado',
                        3 => 'Preparando',
                        4 => 'Listo',
                        5 => 'Repartidor',
                        6 => 'En camino',
                        7 => 'Entregado',
                    ];
                @endphp
                <section class="bg-[#121728] rounded-3xl border border-white/10 overflow-hidden shadow-2xl" data-sub-order-id="{{ $subOrder->id }}">
                    <div class="px-6 py-4 bg-gradient-to-r from-[#23145C] via-[#171E33] to-[#0E2944] border-b border-white/10 text-white flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-xs text-[#00F0FF] font-mono">{{ $subOrder->sub_order_code }}</p>
                            <h2 class="font-display text-lg font-bold">{{ $subOrder->branch->store->business_name }} · {{ $subOrder->branch->branch_name }}</h2>
                        </div>
                        <div class="flex items-center gap-3">
                            @if ($subOrder->estimated_delivery_at && ! $subOrder->status->isTerminal())
                                <span class="text-xs px-3 py-1 rounded-full bg-white/10 border border-white/15 text-[#FF6B8B] font-bold">
                                    ETA ~{{ $subOrder->estimated_delivery_at->format('H:i') }}
                                </span>
                            @endif
                            <span data-status-badge class="px-3 py-1 rounded-full text-xs font-bold border {{ $subOrder->status->badgeClasses() }}">
                                {{ $subOrder->status->label() }}
                            </span>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <!-- Barra de Progreso de 7 Etapas -->
                        @if ($subOrder->status !== \App\Enums\SubOrderStatus::Cancelled)
                            <div class="grid grid-cols-2 sm:grid-cols-7 gap-2">
                                @foreach ($steps as $idx => $label)
                                    @php
                                        $reached = $step >= $idx;
                                    @endphp
                                    <div class="p-2.5 rounded-2xl border text-center transition {{ $reached ? 'bg-[#00F0FF]/15 border-[#00F0FF]/50 text-[#00F0FF]' : 'bg-[#0B0E17]/60 border-white/10 text-slate-500' }}">
                                        <span class="block text-xs font-extrabold">{{ $idx }}</span>
                                        <span class="block text-[11px] font-semibold mt-0.5">{{ $label }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-4 rounded-2xl bg-[#FF3366]/15 border border-[#FF3366]/40 text-[#FF85A3] text-sm font-semibold">
                                Este sub-pedido fue cancelado.
                            </div>
                        @endif

                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                            <!-- Artículos y Repartidor -->
                            <div class="lg:col-span-6 space-y-4">
                                <div class="bg-[#0B0E17]/75 rounded-2xl p-4 border border-white/10 space-y-2">
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Artículos en este envío</h3>
                                    @foreach ($subOrder->items as $item)
                                        <div class="flex justify-between text-sm">
                                            <span class="text-slate-200 font-medium">{{ $item->quantity }}x {{ $item->product_name }}</span>
                                            <span class="font-bold text-white">${{ number_format((float) $item->total_price, 2) }}</span>
                                        </div>
                                    @endforeach
                                    <div class="pt-2 border-t border-white/10 flex justify-between text-xs text-slate-400">
                                        <span>Subtotal + Envío (${{ number_format((float) $subOrder->delivery_fee, 2) }})</span>
                                        <span class="font-bold text-[#00F0FF]">${{ number_format((float) $subOrder->subtotal + (float) $subOrder->delivery_fee, 2) }}</span>
                                    </div>
                                </div>

                                <div class="bg-[#6C28FE]/15 rounded-2xl p-4 border border-[#6C28FE]/40 space-y-2" data-driver-box>
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#00F0FF]">Repartidor JIHOVA Delivery</h3>
                                        <span class="text-xs font-mono font-bold text-white bg-[#0B0E17] px-2.5 py-0.5 rounded-lg border border-[#6C28FE]/50">
                                            PIN entrega: {{ $subOrder->delivery_pin }}
                                        </span>
                                    </div>
                                    @if ($subOrder->driver)
                                        <p class="text-sm font-bold text-white">🛵 {{ $subOrder->driver->full_name }}</p>
                                        <p class="text-xs text-slate-300">
                                            {{ $subOrder->driver->driverProfile?->vehicle_type?->label() ?? 'Moto' }}
                                            @if ($subOrder->driver->driverProfile?->license_plate)
                                                · Placa {{ $subOrder->driver->driverProfile->license_plate }}
                                            @endif
                                            · Tel: {{ $subOrder->driver->phone }}
                                        </p>
                                    @else
                                        <p class="text-xs text-slate-300">Esperando que la tienda finalice la preparación para asignar un repartidor cercano.</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Mapa Esquemático de Ruta + Bitácora Cronológica -->
                            <div class="lg:col-span-6 space-y-4">
                                <!-- Visualizador de Ruta GPS -->
                                @php
                                    $bLat = (float) $subOrder->branch->latitude;
                                    $bLng = (float) $subOrder->branch->longitude;
                                @endphp
                                <div class="bg-[#0B0E17] rounded-2xl p-4 text-white space-y-2 border border-white/10">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-semibold text-slate-300">Mapa de Ruta Hiperlocal</span>
                                        <span class="font-mono text-[11px] text-[#00F0FF]">
                                            🏪 ({{ number_format($bLat, 3) }}, {{ number_format($bLng, 3) }}) → 🏠 ({{ number_format($destLat, 3) }}, {{ number_format($destLng, 3) }})
                                        </span>
                                    </div>
                                    <svg viewBox="0 0 360 110" class="w-full h-24 bg-[#121728] rounded-xl border border-white/10">
                                        <line x1="46" y1="55" x2="314" y2="55" stroke="#26314F" stroke-width="4" stroke-dasharray="6 6" />
                                        @php
                                            $progressX = match ($subOrder->status) {
                                                \App\Enums\SubOrderStatus::DriverAssigned => 120,
                                                \App\Enums\SubOrderStatus::OutForDelivery => 220,
                                                \App\Enums\SubOrderStatus::Delivered => 314,
                                                default => 46,
                                            };
                                        @endphp
                                        <line x1="46" y1="55" x2="{{ $progressX }}" y2="55" stroke="#00F0FF" stroke-width="4" />
                                        <circle cx="46" cy="55" r="12" fill="#6C28FE" />
                                        <text x="46" y="59" text-anchor="middle" font-size="11" fill="#fff">🏪</text>
                                        <text x="46" y="88" text-anchor="middle" font-size="9" fill="#94a3b8">Tienda</text>

                                        <circle cx="314" cy="55" r="12" fill="#FF3366" />
                                        <text x="314" y="59" text-anchor="middle" font-size="11" fill="#fff">🏠</text>
                                        <text x="314" y="88" text-anchor="middle" font-size="9" fill="#94a3b8">Tu casa</text>

                                        <circle cx="{{ $progressX }}" cy="32" r="11" fill="#00F0FF" />
                                        <text x="{{ $progressX }}" y="36" text-anchor="middle" font-size="11" fill="#0B0E17">🛵</text>
                                    </svg>
                                </div>

                                <!-- Bitácora Cronológica -->
                                <div class="bg-[#0B0E17]/75 rounded-2xl p-4 border border-white/10 space-y-2.5" data-events-list>
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Bitácora de entrega</h3>
                                    <ul class="space-y-2 text-xs">
                                        @foreach ($subOrder->trackingEvents as $event)
                                            <li class="flex items-start gap-2.5">
                                                <span class="font-mono font-bold text-[#00F0FF] shrink-0">{{ $event->event_timestamp->format('H:i') }}</span>
                                                <span class="text-slate-300">{{ $event->notes }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            @endforeach
        </div>
    </div>

    @if ($hasActiveSubOrders)
        @push('scripts')
        <script>
            (function () {
                const statusUrl = @json(route('orders.status', $order));
                setInterval(async () => {
                    try {
                        const res = await fetch(statusUrl, { headers: { Accept: 'application/json' } });
                        if (!res.ok) return;
                        const data = await res.json();
                        const allTerminal = (data.sub_orders || []).every((so) => ['delivered', 'cancelled'].includes(so.status));
                        (data.sub_orders || []).forEach((so) => {
                            const card = document.querySelector(`[data-sub-order-id="${so.id}"]`);
                            if (!card) return;
                            const badge = card.querySelector('[data-status-badge]');
                            if (badge && badge.textContent.trim() !== so.status_label) {
                                window.location.reload();
                            }
                        });
                        if (allTerminal) {
                            window.location.reload();
                        }
                    } catch (err) {
                        console.debug(err);
                    }
                }, 8000);
            })();
        </script>
        @endpush
    @endif
</x-layouts.mall>
