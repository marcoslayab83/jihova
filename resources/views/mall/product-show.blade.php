<x-layouts.mall :title="$product->name.' · '.$store->business_name">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10">
        <!-- Breadcrumb -->
        <nav class="text-xs text-slate-400 flex items-center gap-2">
            <a href="{{ route('mall.home') }}" class="hover:text-[#00F0FF] transition">Inicio</a>
            <span>/</span>
            <a href="{{ route('mall.stores.show', $store) }}" class="hover:text-[#00F0FF] transition">{{ $store->business_name }}</a>
            <span>/</span>
            <span class="text-white font-semibold">{{ $product->name }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Imagen Spotlight -->
            <div class="lg:col-span-6 relative group bg-[#121728] rounded-[2rem] border border-white/15 overflow-hidden shadow-2xl p-3">
                <div class="relative rounded-2xl overflow-hidden bg-[#171E33]">
                    <img src="{{ $product->image_url ?? '/images/catalog/moda.jpg' }}" alt="{{ $product->name }}" class="w-full aspect-[4/3] object-cover group-hover:scale-105 transition duration-700">
                    @if ($product->badge)
                        <span class="absolute top-4 left-4 px-3.5 py-1.5 rounded-full bg-[#0B0E17]/85 backdrop-blur border border-[#6C28FE]/50 text-white text-xs font-bold">
                            ✦ {{ $product->badge }}
                        </span>
                    @endif
                    @if ($product->prep_note)
                        <span class="absolute bottom-4 left-4 px-3.5 py-1.5 rounded-full bg-[#0B0E17]/85 backdrop-blur border border-[#00F0FF]/45 text-[#00F0FF] text-xs font-bold shadow-md">
                            ⚡ {{ $product->prep_note }}
                        </span>
                    @endif
                </div>
            </div>

            <!-- Info y compra -->
            <div class="lg:col-span-6 bg-[#121728]/95 backdrop-blur-md rounded-[2rem] border border-white/10 p-6 sm:p-8 space-y-6 shadow-2xl">
                <div class="space-y-2.5">
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('mall.stores.show', $store) }}" class="px-3 py-1 rounded-full bg-[#6C28FE]/20 border border-[#6C28FE]/45 text-[#00F0FF] text-xs font-bold hover:bg-[#6C28FE]/35 transition">
                            🏪 {{ $store->business_name }}
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

                    <h1 class="font-display text-2xl sm:text-4xl font-extrabold text-white tracking-tight">{{ $product->name }}</h1>
                    <p class="text-sm sm:text-base text-slate-300 leading-relaxed">{{ $product->description }}</p>
                </div>

                <div class="space-y-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-[#00F0FF]">Elige la sucursal de despacho</h2>
                    @foreach ($branchOffers as $offer)
                        <div class="p-4 rounded-2xl border {{ $offer['is_available'] ? 'border-white/15 bg-[#0B0E17]/75' : 'border-white/5 bg-[#0B0E17]/40 opacity-55' }} space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-bold text-sm text-white">{{ $offer['branch']->branch_name }}</p>
                                    <p class="text-xs text-slate-400">{{ $offer['branch']->address }}</p>
                                    <p class="text-xs mt-1 font-semibold {{ $offer['in_coverage'] ? 'text-emerald-400' : 'text-amber-400' }}">
                                        ⏱️ {{ $offer['eta_minutes'] }} min · {{ $offer['distance_km'] }} km · Envío ${{ number_format($offer['delivery_fee'], 2) }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="text-2xl font-extrabold text-white">${{ number_format($offer['price'], 2) }}</span>
                                    <span class="block text-[11px] text-slate-400">Stock: {{ $offer['stock'] }}</span>
                                </div>
                            </div>

                            @if ($offer['is_available'])
                                <form action="{{ route('cart.add') }}" method="POST" data-ajax-cart class="flex items-center gap-2">
                                    @csrf
                                    <input type="hidden" name="branch_id" value="{{ $offer['branch']->id }}">
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="number" name="quantity" value="1" min="1" max="{{ $offer['stock'] }}" class="w-20 px-3 py-2.5 rounded-xl border border-white/15 bg-[#121728] text-white text-sm font-bold text-center focus:outline-none focus:border-[#00F0FF]">
                                    <button type="submit" class="flex-1 py-3 px-4 rounded-2xl bg-[#FF3366] hover:bg-[#ff4d7a] text-white text-sm font-bold shadow-lg shadow-[#FF3366]/30 transition cursor-pointer">
                                        Agregar al carrito
                                    </button>
                                </form>
                            @else
                                <span class="inline-block text-xs font-semibold text-[#FF6B8B]">No disponible en esta sucursal</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-layouts.mall>
