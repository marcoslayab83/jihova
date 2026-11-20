<x-layouts.mall title="Mi Carrito · JIHOVA">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-[#00F0FF]">Compra Multi-Tienda</p>
                <h1 class="font-display text-2xl sm:text-3xl font-extrabold text-white">Tu Carrito del Centro Comercial</h1>
                <p class="text-sm text-slate-400">Agrupamos tus artículos automáticamente por tienda para preparar cada envío.</p>
            </div>
            @if ($cart['items_count'] > 0)
                <form action="{{ route('cart.clear') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 rounded-xl border border-[#FF3366]/40 text-[#FF6B8B] hover:bg-[#FF3366]/15 text-xs font-bold cursor-pointer transition">
                        Vaciar carrito
                    </button>
                </form>
            @endif
        </div>

        @if ($cart['warnings'] !== [])
            <div class="bg-amber-500/15 border border-amber-400/35 rounded-2xl p-4 text-sm text-amber-200 space-y-1">
                @foreach ($cart['warnings'] as $warning)
                    <p>⚠️ {{ $warning }}</p>
                @endforeach
            </div>
        @endif

        @if ($cart['items_count'] === 0)
            <div class="bg-[#121728] rounded-3xl border border-white/10 p-14 text-center space-y-4">
                <p class="text-4xl">🛍️</p>
                <h2 class="font-display text-xl font-bold text-white">Aún no tienes artículos en tu carrito</h2>
                <p class="text-sm text-slate-400 max-w-md mx-auto">Busca un desayuno, unos zapatos o lo que necesites en nuestras tiendas abiertas.</p>
                <a href="{{ route('mall.explore') }}" class="inline-flex px-6 py-3 rounded-2xl bg-[#FF3366] hover:bg-[#ff4d7a] text-white text-sm font-bold shadow-lg shadow-[#FF3366]/30 transition">
                    Explorar el Centro Comercial
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <div class="lg:col-span-8 space-y-5">
                    @foreach ($cart['store_groups'] as $group)
                        <section class="bg-[#121728] rounded-3xl border border-white/10 overflow-hidden shadow-xl">
                            <div class="px-5 py-4 bg-[#171E33] border-b border-white/10 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <a href="{{ route('mall.stores.show', $group['store']) }}" class="font-display font-extrabold text-white hover:text-[#00F0FF] transition">
                                        {{ $group['store']->business_name }}
                                    </a>
                                    <p class="text-xs text-slate-400">
                                        {{ $group['branch']->branch_name }} · ~{{ $group['eta_minutes'] }} min · {{ $group['distance_km'] }} km
                                    </p>
                                </div>
                                <div class="text-right text-xs">
                                    <span class="px-3 py-1 rounded-full font-bold border {{ $group['within_radius'] ? 'bg-emerald-500/15 border-emerald-400/35 text-emerald-300' : 'bg-[#FF3366]/15 border-[#FF3366]/40 text-[#FF6B8B]' }}">
                                        {{ $group['within_radius'] ? 'Cobertura OK · Envío $'.number_format($group['delivery_fee'], 2) : 'Fuera de radio de entrega' }}
                                    </span>
                                </div>
                            </div>

                            <div class="divide-y divide-white/10">
                                @foreach ($group['items'] as $item)
                                    <div class="p-3.5 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                                        <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                                            <img src="{{ $item['product']->image_url ?? '/images/catalog/moda.jpg' }}" alt="{{ $item['product']->name }}" class="w-12 h-12 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl object-cover bg-[#171E33] border border-white/10 shrink-0">
                                            <div class="min-w-0">
                                                <a href="{{ route('mall.products.show', [$group['store'], $item['product']]) }}" class="font-bold text-white hover:text-[#00F0FF] block truncate text-sm sm:text-base transition">
                                                    {{ $item['product']->name }}
                                                </a>
                                                <p class="text-xs text-slate-400">${{ number_format($item['unit_price'], 2) }} por unidad</p>
                                            </div>
                                        </div>

                                        <div class="flex items-center justify-between sm:justify-end gap-2 sm:gap-4 pt-2 sm:pt-0 border-t border-white/5 sm:border-0">
                                            <form action="{{ route('cart.update', [$group['branch']->id, $item['product']->id]) }}" method="POST" class="flex items-center gap-1.5">
                                                @csrf
                                                @method('PATCH')
                                                <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="0" max="50" class="w-14 sm:w-16 px-2 py-1 sm:py-1.5 rounded-xl border border-white/15 bg-[#0B0E17] text-white text-center text-xs sm:text-sm font-bold focus:outline-none focus:border-[#00F0FF]">
                                                <button type="submit" class="px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-xs font-semibold text-white cursor-pointer transition">
                                                    Actualizar
                                                </button>
                                            </form>

                                            <span class="font-extrabold text-white text-sm sm:text-base min-w-[70px] text-right">${{ number_format($item['total_price'], 2) }}</span>

                                            <form action="{{ route('cart.remove', [$group['branch']->id, $item['product']->id]) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs font-bold text-[#FF6B8B] hover:text-[#FF3366] cursor-pointer">Quitar</button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>

                <!-- Resumen Lateral -->
                <aside class="lg:col-span-4 bg-[#121728] rounded-3xl border border-white/10 p-6 space-y-5 sticky top-24 shadow-xl">
                    <h2 class="font-display text-lg font-extrabold text-white">Resumen del pedido</h2>

                    <div class="space-y-2.5 text-sm">
                        <div class="flex justify-between text-slate-300">
                            <span>Artículos ({{ $cart['items_count'] }})</span>
                            <span class="font-semibold text-white">${{ number_format($cart['total_items_amount'], 2) }}</span>
                        </div>
                        <div class="flex justify-between text-slate-300">
                            <span>Delivery ({{ $cart['stores_count'] }} {{ $cart['stores_count'] === 1 ? 'tienda' : 'tiendas' }})</span>
                            <span class="font-semibold text-white">${{ number_format($cart['total_delivery_fee'], 2) }}</span>
                        </div>
                        <div class="flex justify-between text-slate-300">
                            <span>Tarifa de servicio JIHOVA</span>
                            <span class="font-semibold text-white">${{ number_format($cart['platform_fee'], 2) }}</span>
                        </div>
                        <div class="pt-3 border-t border-white/10 flex justify-between text-lg font-extrabold text-white">
                            <span>Total</span>
                            <span class="text-[#00F0FF]">${{ number_format($cart['grand_total'], 2) }}</span>
                        </div>
                    </div>

                    <a href="{{ route('checkout.show') }}" class="block w-full text-center py-3.5 rounded-2xl bg-[#FF3366] hover:bg-[#ff4d7a] text-white font-bold text-sm shadow-lg shadow-[#FF3366]/30 transition">
                        Continuar al pago →
                    </a>
                </aside>
            </div>
        @endif
    </div>
</x-layouts.mall>
