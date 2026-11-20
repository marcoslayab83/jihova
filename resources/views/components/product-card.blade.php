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
    $priceBs = number_format($offer['price'] * 2000, 0, ',', '.');
@endphp

<article class="group bg-[#121728]/95 backdrop-blur-md rounded-2xl sm:rounded-3xl border border-white/10 overflow-hidden shadow-lg hover:shadow-[0_18px_42px_-16px_rgba(0,240,255,0.28)] hover:-translate-y-1 hover:border-[#00F0FF]/55 transition-all duration-300 flex flex-col">
    <div class="p-2 sm:p-2.5 pb-0">
        <a href="{{ route('mall.products.show', [$store, $product]) }}" class="relative block aspect-[4/3] rounded-xl sm:rounded-2xl bg-[#171E33] overflow-hidden border border-white/10">
            <img
                src="{{ $product->image_url ?? '/images/catalog/moda.jpg' }}"
                alt="{{ $product->name }}"
                class="w-full h-full object-cover group-hover:scale-110 transition duration-700"
                loading="lazy"
            >
            <div class="absolute inset-0 bg-gradient-to-t from-[#0B0E17]/80 via-transparent to-transparent opacity-70 group-hover:opacity-85 transition"></div>

            @if ($product->badge)
                <span class="absolute top-1.5 left-1.5 sm:top-2.5 sm:left-2.5 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full bg-[#0B0E17]/85 backdrop-blur-md border border-[#6C28FE]/50 text-white text-[9px] sm:text-[10px] font-bold tracking-wide shadow-sm truncate max-w-[85%]">
                    ✦ {{ $product->badge }}
                </span>
            @endif

            <span class="absolute bottom-1.5 left-1.5 sm:bottom-2.5 sm:left-2.5 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full bg-[#0B0E17]/85 backdrop-blur-md border border-[#00F0FF]/35 text-[#00F0FF] text-[9px] sm:text-[10px] font-bold">
                ⏱️ {{ $offer['eta_minutes'] }}m
            </span>

            @if ($product->prep_note)
                <span class="hidden sm:inline-block absolute bottom-2.5 right-2.5 px-2 py-0.5 rounded-full bg-[#6C28FE]/80 backdrop-blur-md text-white text-[10px] font-semibold">
                    {{ $product->prep_note }}
                </span>
            @endif
        </a>
    </div>

    <div class="p-2.5 sm:p-4 flex-1 flex flex-col justify-between gap-2 sm:gap-3">
        <div class="space-y-1">
            <div class="flex items-center justify-between gap-1 text-[10px] sm:text-[11px] font-semibold">
                <a href="{{ route('mall.stores.show', $store) }}" class="text-[#00F0FF] hover:text-white truncate flex items-center gap-1 transition">
                    <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full shrink-0 ring-2 ring-white/20" style="background-color: {{ $store->accent_color ?? '#00F0FF' }};"></span>
                    <span class="truncate">{{ $store->business_name }}</span>
                </a>
                @if ($category)
                    <span class="hidden sm:inline-block px-2 py-0.5 rounded-full bg-white/5 border border-white/10 text-slate-300 shrink-0 text-[10px]">{{ $category->name }}</span>
                @endif
            </div>

            <a href="{{ route('mall.products.show', [$store, $product]) }}" class="block">
                <h3 class="font-display font-bold text-[#F3F4F6] text-xs sm:text-base leading-snug line-clamp-2 group-hover:text-[#00F0FF] transition">
                    {{ $product->name }}
                </h3>
            </a>

            <p class="hidden sm:block text-xs text-slate-400 line-clamp-2">{{ $product->description }}</p>
        </div>

        <div class="pt-2 sm:pt-3 border-t border-white/10 flex items-center justify-between gap-1.5">
            <div>
                <span class="block text-base sm:text-xl font-black text-white tracking-tight">${{ number_format($offer['price'], 2) }}</span>
                <span class="block text-[10px] sm:text-[11px] font-bold text-[#00F0FF]">
                    Bs. {{ $priceBs }}
                </span>
            </div>

            @if ($isOpen && $inStock)
                <form action="{{ route('cart.add') }}" method="POST" data-ajax-cart>
                    @csrf
                    <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="px-2.5 py-1.5 sm:px-4 sm:py-2 rounded-xl sm:rounded-2xl bg-[#FF3366] hover:bg-[#ff4d7a] text-white text-[11px] sm:text-xs font-bold transition shadow-md shadow-[#FF3366]/30 hover:shadow-[#FF3366]/50 cursor-pointer">
                        + Pedir
                    </button>
                </form>
            @else
                <span class="px-2 py-1 sm:px-3 sm:py-1.5 rounded-lg sm:rounded-xl bg-white/5 border border-white/10 text-slate-400 text-[10px] sm:text-xs font-semibold">
                    {{ $isOpen ? 'Agotado' : 'Cerrado' }}
                </span>
            @endif
        </div>
    </div>
</article>
