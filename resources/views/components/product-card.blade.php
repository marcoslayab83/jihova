@props([
    'offer',
])

@php
    $product = $offer['product'];
    $store = $offer['store'];
    $branch = $offer['branch'];
    $category = $offer['category'];
    $isOpen = (bool) $branch->is_open;
    $inStock = (int) $offer['stock'] > 0;
@endphp

<article class="group bg-[#121728]/95 backdrop-blur-md rounded-3xl border border-white/10 overflow-hidden shadow-lg hover:shadow-[0_18px_42px_-16px_rgba(0,240,255,0.28)] hover:-translate-y-1 hover:border-[#00F0FF]/55 transition-all duration-300 flex flex-col">
    <div class="p-2.5 pb-0">
        <a href="{{ route('mall.products.show', [$store, $product]) }}" class="relative block aspect-[4/3] rounded-2xl bg-[#171E33] overflow-hidden border border-white/10">
            <img
                src="{{ $product->image_url ?? '/images/catalog/moda.jpg' }}"
                alt="{{ $product->name }}"
                class="w-full h-full object-cover group-hover:scale-110 transition duration-700"
            >
            <div class="absolute inset-0 bg-gradient-to-t from-[#0B0E17]/80 via-transparent to-transparent opacity-70 group-hover:opacity-85 transition"></div>

            @if ($product->badge)
                <span class="absolute top-2.5 left-2.5 px-2.5 py-1 rounded-full bg-[#0B0E17]/85 backdrop-blur-md border border-[#6C28FE]/50 text-white text-[10px] font-bold tracking-wide shadow-sm">
                    ✦ {{ $product->badge }}
                </span>
            @endif

            <span class="absolute bottom-2.5 left-2.5 px-2.5 py-1 rounded-full bg-[#0B0E17]/85 backdrop-blur-md border border-[#00F0FF]/35 text-[#00F0FF] text-[10px] font-bold">
                ⏱️ {{ $offer['eta_minutes'] }} min · {{ number_format($offer['distance_km'], 1) }} km
            </span>

            @if ($product->prep_note)
                <span class="absolute bottom-2.5 right-2.5 px-2 py-0.5 rounded-full bg-[#6C28FE]/80 backdrop-blur-md text-white text-[10px] font-semibold">
                    {{ $product->prep_note }}
                </span>
            @endif
        </a>
    </div>

    <div class="p-4 flex-1 flex flex-col justify-between gap-3">
        <div class="space-y-1.5">
            <div class="flex items-center justify-between gap-2 text-[11px] font-semibold">
                <a href="{{ route('mall.stores.show', $store) }}" class="text-[#00F0FF] hover:text-white truncate flex items-center gap-1.5 transition">
                    <span class="w-2 h-2 rounded-full shrink-0 ring-2 ring-white/20" style="background-color: {{ $store->accent_color ?? '#00F0FF' }};"></span>
                    {{ $store->business_name }}
                </a>
                @if ($category)
                    <span class="px-2 py-0.5 rounded-full bg-white/5 border border-white/10 text-slate-300 shrink-0 text-[10px]">{{ $category->name }}</span>
                @endif
            </div>

            <a href="{{ route('mall.products.show', [$store, $product]) }}" class="block">
                <h3 class="font-display font-bold text-[#F3F4F6] text-base leading-snug line-clamp-2 group-hover:text-[#00F0FF] transition">
                    {{ $product->name }}
                </h3>
            </a>

            <p class="text-xs text-slate-400 line-clamp-2">{{ $product->description }}</p>
        </div>

        <div class="pt-3 border-t border-white/10 flex items-center justify-between gap-2">
            <div>
                <span class="block text-xl font-extrabold text-white tracking-tight">${{ number_format($offer['price'], 2) }}</span>
                <span class="block text-[11px] font-semibold {{ $offer['in_coverage'] ? 'text-emerald-400' : 'text-amber-400' }}">
                    {{ $offer['in_coverage'] ? 'Envío $'.number_format($offer['delivery_fee'], 2) : 'Fuera de radio cercano' }}
                </span>
            </div>

            @if ($isOpen && $inStock)
                <form action="{{ route('cart.add') }}" method="POST" data-ajax-cart>
                    @csrf
                    <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="px-4 py-2 rounded-2xl bg-[#FF3366] hover:bg-[#ff4d7a] text-white text-xs font-bold transition shadow-md shadow-[#FF3366]/30 hover:shadow-[#FF3366]/50 cursor-pointer">
                        + Agregar
                    </button>
                </form>
            @else
                <span class="px-3 py-1.5 rounded-xl bg-white/5 border border-white/10 text-slate-400 text-xs font-semibold">
                    {{ $isOpen ? 'Agotado' : 'Cerrado' }}
                </span>
            @endif
        </div>
    </div>
</article>
