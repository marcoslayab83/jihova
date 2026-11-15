<x-layouts.mall :title="$product->name.' · '.$store->business_name">
    @php
        $cleanPhone = preg_replace('/[^0-9]/', '', $store->phone ?? '');
        $waProductMsg = urlencode("¡Hola {$store->business_name}! Estoy viendo {$product->name} en JIHOVA y quiero ordenar / consultar.");
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10">
        <!-- Breadcrumb -->
        <nav class="text-xs text-slate-400 flex items-center gap-2">
            <a href="{{ route('mall.home') }}" class="hover:text-[#00F0FF] transition">Inicio</a>
            <span>/</span>
            <a href="{{ route('mall.stores.show', $store) }}" class="hover:text-[#00F0FF] transition flex items-center gap-1.5">
                @if ($store->logo_url)
                    <img src="{{ $store->logo_url }}" alt="{{ $store->business_name }}" class="w-4 h-4 rounded-full object-cover">
                @endif
                {{ $store->business_name }}
            </a>
            <span>/</span>
            <span class="text-white font-semibold">{{ $product->name }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Imagen Spotlight de Alta Resolución -->
            <div class="lg:col-span-6 relative group bg-[#121728] rounded-[2rem] border border-white/15 overflow-hidden shadow-2xl p-3">
                <div class="relative rounded-2xl overflow-hidden bg-[#171E33]">
                    <img
                        src="{{ $product->image_url ?? '/images/catalog/desayuno.jpg' }}"
                        alt="{{ $product->name }}"
                        class="w-full aspect-[4/3] object-cover group-hover:scale-105 transition duration-700"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-[#0B0E17]/70 via-transparent to-transparent"></div>
                    
                    @if ($product->badge)
                        <span class="absolute top-4 left-4 px-3.5 py-1.5 rounded-full bg-[#0B0E17]/90 backdrop-blur border border-[#FF6600]/60 text-white text-xs font-bold shadow-lg">
                            ✦ {{ $product->badge }}
                        </span>
                    @endif
                    @if ($product->prep_note)
                        <span class="absolute bottom-4 left-4 px-3.5 py-1.5 rounded-full bg-[#0B0E17]/90 backdrop-blur border border-[#00F0FF]/45 text-[#00F0FF] text-xs font-bold shadow-md">
                            ⚡ {{ $product->prep_note }}
                        </span>
                    @endif
                </div>

                <!-- Garantías de Calidad -->
                <div class="grid grid-cols-3 gap-2 mt-3 pt-3 border-t border-white/10 text-center text-[11px] text-slate-300">
                    <div class="p-2 rounded-xl bg-white/5">
                        <span class="block text-base mb-0.5">🥟</span>
                        <span>Masa Artesanal</span>
                    </div>
                    <div class="p-2 rounded-xl bg-white/5">
                        <span class="block text-base mb-0.5">⚡</span>
                        <span>Frito al Momento</span>
                    </div>
                    <div class="p-2 rounded-xl bg-white/5">
                        <span class="block text-base mb-0.5">🛵</span>
                        <span>Entrega Rápida</span>
                    </div>
                </div>
            </div>

            <!-- Info y Compra -->
            <div class="lg:col-span-6 bg-[#121728]/95 backdrop-blur-md rounded-[2rem] border border-white/10 p-6 sm:p-8 space-y-6 shadow-2xl">
                <div class="space-y-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('mall.stores.show', $store) }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#6C28FE]/20 border border-[#6C28FE]/45 text-[#00F0FF] text-xs font-bold hover:bg-[#6C28FE]/35 transition">
                            @if ($store->logo_url)
                                <img src="{{ $store->logo_url }}" alt="{{ $store->business_name }}" class="w-3.5 h-3.5 rounded-full object-cover">
                            @else
                                <span>🏪</span>
                            @endif
                            {{ $store->business_name }}
                        </a>
                        @if ($product->category)
                            <span class="px-3 py-1 rounded-full bg-white/5 border border-white/10 text-slate-300 text-xs font-semibold">
                                {{ $product->category->name }}
                            </span>
                        @endif
                        @if ($product->sku)
                            <span class="text-xs font-mono text-slate-400">SKU: {{ $product->sku }}</span>
                        @endif
                    </div>

                    <h1 class="font-display text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
                        {{ $product->name }}
                    </h1>
                    <p class="text-sm sm:text-base text-slate-300 leading-relaxed">
                        {{ $product->description }}
                    </p>
                </div>

                <div class="space-y-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-[#00F0FF]">
                        Elige la sucursal de despacho
                    </h2>
                    @foreach ($branchOffers as $offer)
                        @php
                            $priceBs = number_format($offer['price'] * 2000, 0, ',', '.');
                        @endphp
                        <div class="p-4 sm:p-5 rounded-2xl border {{ $offer['is_available'] ? 'border-white/15 bg-[#0B0E17]/75' : 'border-white/5 bg-[#0B0E17]/40 opacity-55' }} space-y-3.5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-bold text-sm text-white">{{ $offer['branch']->branch_name }}</p>
                                    <p class="text-xs text-slate-400">{{ $offer['branch']->address }}</p>
                                    <p class="text-xs mt-1 font-semibold {{ $offer['in_coverage'] ? 'text-emerald-400' : 'text-amber-400' }}">
                                        ⏱️ {{ $offer['eta_minutes'] }} min · {{ number_format($offer['distance_km'], 1) }} km · Envío ${{ number_format($offer['delivery_fee'], 2) }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="text-2xl sm:text-3xl font-black text-white leading-tight">
                                        ${{ number_format($offer['price'], 2) }}
                                    </span>
                                    <span class="block text-xs font-bold text-[#00F0FF]">
                                        Bs. {{ $priceBs }}
                                    </span>
                                    <span class="block text-[11px] text-slate-400 mt-0.5">
                                        Stock: {{ $offer['stock'] }} disponibles
                                    </span>
                                </div>
                            </div>

                            @if ($offer['is_available'])
                                <form action="{{ route('cart.add') }}" method="POST" data-ajax-cart class="flex items-center gap-2 pt-1">
                                    @csrf
                                    <input type="hidden" name="branch_id" value="{{ $offer['branch']->id }}">
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <div class="flex items-center bg-[#121728] border border-white/15 rounded-xl overflow-hidden shrink-0">
                                        <button type="button" class="px-2.5 py-2.5 text-slate-300 hover:text-white font-bold text-sm" onclick="const input = this.parentElement.querySelector('input'); if(input.value > 1) input.value--;">-</button>
                                        <input type="number" name="quantity" value="1" min="1" max="{{ $offer['stock'] }}" class="w-12 text-center bg-transparent text-sm font-bold text-white focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                        <button type="button" class="px-2.5 py-2.5 text-slate-300 hover:text-white font-bold text-sm" onclick="const input = this.parentElement.querySelector('input'); if(input.value < {{ $offer['stock'] }}) input.value++;">+</button>
                                    </div>
                                    <button type="submit" class="flex-1 py-3 px-5 rounded-2xl bg-[#FF3366] hover:bg-[#ff4d7a] text-white text-sm font-bold shadow-lg shadow-[#FF3366]/30 transition cursor-pointer">
                                        + Agregar al Carrito
                                    </button>
                                </form>
                            @else
                                <span class="inline-block text-xs font-semibold text-[#FF6B8B]">No disponible en esta sucursal</span>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Contacto WhatsApp Directo con el Comerciante -->
                @if ($cleanPhone)
                    <div class="pt-2 border-t border-white/10 flex items-center justify-between">
                        <span class="text-xs text-slate-400">¿Deseas personalizar tu pedido?</span>
                        <a
                            href="https://wa.me/{{ $cleanPhone }}?text={{ $waProductMsg }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-[#25D366]/20 hover:bg-[#25D366]/30 text-[#25D366] text-xs font-bold border border-[#25D366]/40 transition"
                        >
                            <span>WhatsApp Directo</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.mall>
