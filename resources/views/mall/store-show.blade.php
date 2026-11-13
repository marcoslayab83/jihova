<x-layouts.mall :title="$store->business_name.' · JIHOVA'">
    @php
        $bannerUrl = $store->banner_url ?? '/images/catalog/hero-mall.jpg';
    @endphp

    <!-- Banner Editorial de la Tienda -->
    <section class="relative overflow-hidden bg-[#0B0E17] text-white border-b border-white/10">
        <img src="{{ $bannerUrl }}" alt="{{ $store->business_name }}" class="absolute inset-0 w-full h-full object-cover opacity-35 animate-ken-burns">
        <div class="absolute inset-0" style="background: linear-gradient(120deg, rgba(11,14,23,0.95) 20%, {{ $store->accent_color ?? '#6C28FE' }}aa 100%);"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-start gap-4">
                    <div class="w-18 h-18 rounded-3xl text-white font-display font-bold text-3xl flex items-center justify-center shadow-2xl ring-2 ring-white/30 shrink-0" style="background-color: {{ $store->accent_color ?? '#6C28FE' }};">
                        {{ mb_substr($store->business_name, 0, 1) }}
                    </div>
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            @if ($store->category)
                                <span class="px-3 py-1 rounded-full bg-[#121728]/80 backdrop-blur border border-[#00F0FF]/40 text-xs font-bold text-[#00F0FF]">
                                    {{ $store->category->name }}
                                </span>
                            @endif
                            <span class="px-3 py-1 rounded-full bg-amber-400/20 border border-amber-300/40 text-amber-300 text-xs font-extrabold">
                                ★ {{ number_format((float) $store->rating, 1) }}
                            </span>
                            @if ($activeBranch)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $activeBranch->is_open ? 'bg-emerald-500/20 border-emerald-400/50 text-emerald-300' : 'bg-white/10 border-white/20 text-slate-300' }}">
                                    @if ($activeBranch->is_open)
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    @endif
                                    {{ $activeBranch->is_open ? 'Abierta ahora' : 'Cerrada temporalmente' }}
                                </span>
                            @endif
                        </div>
                        <h1 class="font-display text-3xl sm:text-4xl font-extrabold text-white tracking-tight">{{ $store->business_name }}</h1>
                        <p class="text-sm sm:text-base text-slate-200 max-w-2xl">{{ $store->description ?? $store->tagline }}</p>
                    </div>
                </div>

                @if ($activeBranch && $branchMetrics)
                    <div class="bg-[#121728]/85 backdrop-blur-xl rounded-3xl border border-white/15 p-5 shadow-2xl min-w-[270px] space-y-2 text-xs text-white">
                        <p class="font-display font-bold text-sm text-[#00F0FF]">{{ $activeBranch->branch_name }}</p>
                        <p class="text-slate-300">{{ $activeBranch->address }}</p>
                        <div class="pt-2.5 border-t border-white/10 flex items-center justify-between font-bold">
                            <span>⏱️ ~{{ $branchMetrics['eta_minutes'] }} min ({{ $branchMetrics['distance_km'] }} km)</span>
                            <span class="text-[#FF3366]">Envío ${{ number_format($branchMetrics['delivery_fee'], 2) }}</span>
                        </div>
                    </div>
                @endif
            </div>

            @if ($store->branches->count() > 1)
                <div class="flex flex-wrap items-center gap-2 pt-2">
                    <span class="text-xs font-semibold text-slate-300">Sucursal:</span>
                    @foreach ($store->branches as $branch)
                        <a
                            href="{{ route('mall.stores.show', ['store' => $store, 'branch' => $branch->id]) }}"
                            class="px-4 py-1.5 rounded-full text-xs font-bold border transition {{ $activeBranch?->id === $branch->id ? 'bg-[#FF3366] text-white border-[#FF3366] shadow-md shadow-[#FF3366]/30' : 'bg-[#121728]/80 text-slate-200 border-white/20 hover:border-[#00F0FF]' }}"
                        >
                            {{ $branch->branch_name }} {{ $branch->is_open ? '●' : '(Cerrada)' }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <!-- Catálogo de la Tienda -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="font-display text-2xl font-bold text-white">Catálogo de {{ $store->business_name }}</h2>
            <span class="text-xs font-bold px-3 py-1 rounded-full bg-[#121728] border border-white/15 text-[#00F0FF]">{{ $products->count() }} artículos</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach ($products as $item)
                @php
                    $product = $item['product'];
                @endphp
                <article class="group bg-[#121728]/95 rounded-3xl border border-white/10 overflow-hidden shadow-lg hover:shadow-[0_18px_42px_-16px_rgba(0,240,255,0.28)] hover:border-[#00F0FF]/55 hover:-translate-y-1 transition-all duration-300 flex flex-col">
                    <div class="p-2.5 pb-0">
                        <a href="{{ route('mall.products.show', [$store, $product]) }}" class="aspect-[4/3] rounded-2xl bg-[#171E33] overflow-hidden block relative border border-white/10">
                            <img src="{{ $product->image_url ?? '/images/catalog/moda.jpg' }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-700">
                            <div class="absolute inset-0 bg-gradient-to-t from-[#0B0E17]/75 via-transparent to-transparent"></div>
                            @if ($product->badge)
                                <span class="absolute top-2.5 left-2.5 px-2.5 py-1 rounded-full bg-[#0B0E17]/85 backdrop-blur border border-[#6C28FE]/50 text-white text-[10px] font-bold">
                                    ✦ {{ $product->badge }}
                                </span>
                            @endif
                        </a>
                    </div>
                    <div class="p-4 flex-1 flex flex-col justify-between gap-3">
                        <div class="space-y-1">
                            <a href="{{ route('mall.products.show', [$store, $product]) }}" class="font-display font-bold text-white hover:text-[#00F0FF] transition block">
                                {{ $product->name }}
                            </a>
                            <p class="text-xs text-slate-400 line-clamp-2">{{ $product->description }}</p>
                            @if ($product->prep_note)
                                <p class="text-[11px] font-semibold text-[#00F0FF]">{{ $product->prep_note }}</p>
                            @endif
                        </div>

                        <div class="pt-3 border-t border-white/10 flex items-center justify-between gap-2">
                            <div>
                                <span class="block text-xl font-extrabold text-white">${{ number_format($item['price'], 2) }}</span>
                                <span class="block text-[11px] text-slate-400">Stock: {{ $item['stock'] }}</span>
                            </div>
                            @if ($activeBranch && $item['is_available'])
                                <form action="{{ route('cart.add') }}" method="POST" data-ajax-cart>
                                    @csrf
                                    <input type="hidden" name="branch_id" value="{{ $activeBranch->id }}">
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="px-4 py-2 rounded-2xl bg-[#FF3366] hover:bg-[#ff4d7a] text-white text-xs font-bold shadow-md shadow-[#FF3366]/30 transition cursor-pointer">
                                        + Agregar
                                    </button>
                                </form>
                            @else
                                <span class="px-3 py-1.5 rounded-xl bg-white/5 border border-white/10 text-slate-400 text-xs font-semibold">No disponible</span>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
</x-layouts.mall>
