<x-layouts.mall title="Mis Pedidos · JIHOVA">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-[#00F0FF]">Historial y Seguimiento</p>
                <h1 class="font-display text-2xl sm:text-3xl font-extrabold text-white">Mis Pedidos</h1>
            </div>
            <a href="{{ route('mall.explore') }}" class="px-4 py-2 rounded-xl bg-[#FF3366] hover:bg-[#ff4d7a] text-white text-xs font-bold shadow-md shadow-[#FF3366]/25 transition">
                Seguir comprando
            </a>
        </div>

        @if ($orders->isEmpty())
            <div class="bg-[#121728] rounded-3xl border border-white/10 p-12 text-center space-y-3">
                <p class="text-4xl">📦</p>
                <h2 class="font-display text-lg font-bold text-white">Todavía no has realizado pedidos</h2>
                <p class="text-sm text-slate-400">Explora desayunos, calzado, tecnología y más para recibir tu primer envío.</p>
                <a href="{{ route('mall.explore') }}" class="inline-flex px-5 py-2.5 rounded-xl bg-[#FF3366] hover:bg-[#ff4d7a] text-white text-sm font-bold shadow-lg shadow-[#FF3366]/30 transition">
                    Ir al Centro Comercial
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($orders as $order)
                    <article class="bg-[#121728] rounded-3xl border border-white/10 p-5 shadow-xl space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-white/10">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-white">{{ $order->order_number }}</span>
                                    <x-status-badge :status="$order->payment_status" />
                                </div>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    {{ $order->created_at->format('d/m/Y H:i') }} · {{ $order->delivery_snapshot['title'] ?? 'Dirección guardada' }}
                                </p>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="text-lg font-extrabold text-[#00F0FF]">${{ number_format((float) $order->grand_total, 2) }}</span>
                                <a href="{{ route('orders.show', $order) }}" class="px-4 py-2 rounded-xl bg-[#6C28FE] hover:bg-[#7d42ff] text-white text-xs font-bold shadow-md shadow-[#6C28FE]/30 transition">
                                    Rastrear entrega →
                                </a>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach ($order->subOrders as $subOrder)
                                <div class="p-3.5 rounded-2xl bg-[#0B0E17]/75 border border-white/10 space-y-2">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="font-bold text-xs text-white">🏪 {{ $subOrder->branch->store->business_name }}</span>
                                        <x-status-badge :status="$subOrder->status" />
                                    </div>
                                    <p class="text-xs text-slate-300">
                                        {{ $subOrder->items->map(fn ($i) => $i->quantity.'x '.$i->product_name)->join(', ') }}
                                    </p>
                                    @if ($subOrder->driver)
                                        <p class="text-[11px] text-[#00F0FF] font-semibold">🛵 Repartidor: {{ $subOrder->driver->full_name }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>

            <div>
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</x-layouts.mall>
