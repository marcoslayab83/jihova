@props([
    'item',
])

@php
    $store = $item['store'];
    $branch = $item['best_branch'];
    $isOpen = (int) $item['open_branches_count'] > 0;
    $coverMap = [
        'desayunos-cafe' => '/images/catalog/desayuno.jpg',
        'calzado-moda' => '/images/catalog/tenis.jpg',
        'tecnologia' => '/images/catalog/tecnologia.jpg',
        'farmacia-salud' => '/images/catalog/farmacia.jpg',
        'supermercado-hogar' => '/images/catalog/supermercado.jpg',
    ];
    $coverUrl = $store->banner_url ?? ($coverMap[$store->category?->slug] ?? '/images/catalog/hero-mall.jpg');
@endphp

<a href="{{ route('mall.stores.show', $store) }}" class="group block w-full min-w-[260px] sm:min-w-0 shrink-0 sm:shrink bg-[#121728]/95 backdrop-blur-md rounded-2xl sm:rounded-3xl border border-white/10 overflow-hidden shadow-lg hover:shadow-[0_18px_42px_-16px_rgba(108,40,254,0.45)] hover:-translate-y-1 hover:border-[#6C28FE]/70 transition-all duration-300">
    <div class="h-32 sm:h-36 relative overflow-hidden">
        <img
            src="{{ $coverUrl }}"
            alt="{{ $store->business_name }}"
            class="w-full h-full object-cover group-hover:scale-110 transition duration-700"
        >
        <div class="absolute inset-0 bg-gradient-to-t from-[#0B0E17] via-[#0B0E17]/45 to-transparent"></div>

        <div class="absolute top-3 right-3 flex items-center gap-1.5">
            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#0B0E17]/85 backdrop-blur border border-white/15 text-amber-300 shadow-sm">
                ★ {{ number_format((float) $store->rating, 1) }}
            </span>
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold backdrop-blur border {{ $isOpen ? 'bg-emerald-500/20 border-emerald-400/50 text-emerald-300' : 'bg-white/10 border-white/15 text-slate-300' }}">
                @if ($isOpen)
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                @endif
                {{ $isOpen ? 'Abierta' : 'Cerrada' }}
            </span>
        </div>

        <div class="absolute bottom-3 left-4 right-4 flex items-end justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-11 h-11 rounded-2xl text-white font-display font-bold text-lg flex items-center justify-center shadow-lg ring-2 ring-white/25 shrink-0 overflow-hidden bg-[#121728]" style="background-color: {{ $store->accent_color ?? '#6C28FE' }};">
                    @if ($store->logo_url)
                        <img src="{{ $store->logo_url }}" alt="{{ $store->business_name }}" class="w-full h-full object-cover">
                    @else
                        {{ mb_substr($store->business_name, 0, 1) }}
                    @endif
                </div>
                <div class="min-w-0">
                    <h3 class="font-display font-bold text-white text-base truncate group-hover:text-[#00F0FF] transition">
                        {{ $store->business_name }}
                    </h3>
                    @if ($store->category)
                        <span class="inline-block text-[11px] font-semibold text-[#00F0FF]">
                            {{ $store->category->name }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="p-4 space-y-3">
        <p class="text-xs text-slate-400 line-clamp-2 min-h-[2rem]">
            {{ $store->tagline ?? $store->description }}
        </p>

        @if ($branch)
            <div class="pt-2.5 border-t border-white/10 flex items-center justify-between text-xs text-slate-300">
                <span class="inline-flex items-center gap-1 font-medium text-[#00F0FF]">⏱️ {{ $item['eta_minutes'] }} min · {{ number_format((float) $item['distance_km'], 1) }} km</span>
                <span class="font-bold text-white bg-white/5 border border-white/10 px-2.5 py-1 rounded-full">Envío ${{ number_format((float) $item['delivery_fee'], 2) }}</span>
            </div>
        @endif
    </div>
</a>
