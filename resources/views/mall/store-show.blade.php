<x-layouts.mall :title="$store->business_name.' · JIHOVA'">
    @php
        $bannerUrl = $store->banner_url ?? '/images/catalog/hero-mall.jpg';
        $storeAd = \App\Models\MallAdBanner::where('store_id', $store->id)->where('is_active', true)->first();
        $cleanPhone = preg_replace('/[^0-9]/', '', $store->phone ?? '');
        $waMessage = urlencode("¡Hola {$store->business_name}! Estoy viendo su menú en JIHOVA y quiero hacer una consulta / pedido.");
    @endphp

    <!-- Banner Editorial Hero de la Tienda -->
    <section class="relative overflow-hidden bg-[#0B0E17] text-white border-b border-white/10">
        <!-- Fondo con efecto Ken-Burns suave y atmósfera -->
        <img src="{{ $bannerUrl }}" alt="{{ $store->business_name }}" class="absolute inset-0 w-full h-full object-cover opacity-30 animate-ken-burns">
        <div class="absolute inset-0" style="background: linear-gradient(135deg, rgba(11,14,23,0.96) 15%, rgba(11,14,23,0.85) 55%, {{ $store->accent_color ?? '#6C28FE' }}66 100%);"></div>
        <div class="absolute inset-0 bg-[linear-gradient(to_right,rgba(255,255,255,0.02)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.02)_1px,transparent_1px)] bg-[size:32px_32px]"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-14 space-y-6 sm:space-y-8">
            <!-- Encabezado de la tienda -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 sm:gap-8">
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-6">
                    <!-- Logo Oficial con Halo Neón -->
                    <div class="relative group shrink-0">
                        <div class="absolute -inset-1 rounded-3xl opacity-75 blur-lg transition group-hover:opacity-100" style="background: {{ $store->accent_color ?? '#FF6600' }};"></div>
                        <div class="relative w-20 h-20 sm:w-28 sm:h-28 rounded-3xl overflow-hidden ring-2 ring-white/30 shadow-2xl bg-[#121728] flex items-center justify-center">
                            @if ($store->logo_url)
                                <img src="{{ $store->logo_url }}" alt="{{ $store->business_name }}" class="w-full h-full object-contain p-1.5 transition duration-500 group-hover:scale-105">
                            @else
                                <span class="font-display font-extrabold text-3xl sm:text-4xl text-white">{{ mb_substr($store->business_name, 0, 1) }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Datos Principales -->
                    <div class="space-y-2 sm:space-y-3">
                        <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                            @if ($store->category)
                                <span class="px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full bg-[#121728]/90 backdrop-blur border border-[#00F0FF]/40 text-[10px] sm:text-xs font-bold text-[#00F0FF] shadow-sm">
                                    {{ $store->category->name }}
                                </span>
                            @endif
                            <span class="inline-flex items-center gap-1 px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full bg-emerald-500/20 border border-emerald-400/50 text-emerald-300 text-[10px] sm:text-xs font-bold">
                                <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 fill-current" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                Oficial JIHOVA
                            </span>
                            <span class="px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full bg-amber-400/20 border border-amber-300/40 text-amber-300 text-[10px] sm:text-xs font-extrabold">
                                ★ {{ number_format((float) $store->rating, 1) }}
                            </span>
                            @if ($activeBranch)
                                <span class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full text-[10px] sm:text-xs font-bold border {{ $activeBranch->is_open ? 'bg-emerald-500/20 border-emerald-400/50 text-emerald-300' : 'bg-white/10 border-white/20 text-slate-300' }}">
                                    @if ($activeBranch->is_open)
                                        <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                    @endif
                                    {{ $activeBranch->is_open ? 'Abierta ahora' : 'Cerrada' }}
                                </span>
                            @endif
                        </div>

                        <h1 class="font-display text-2xl sm:text-5xl font-extrabold text-white tracking-tight">
                            {{ $store->business_name }}
                        </h1>

                        <p class="text-xs sm:text-base text-slate-200 max-w-2xl leading-relaxed">
                            {{ $store->description ?? $store->tagline }}
                        </p>

                        <!-- Botonera de Acción Rápida -->
                        <div class="flex flex-wrap items-center gap-2 pt-1 sm:pt-2">
                            @if ($cleanPhone)
                                <a
                                    href="https://wa.me/{{ $cleanPhone }}?text={{ $waMessage }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-1.5 sm:py-2 rounded-2xl bg-[#25D366]/20 hover:bg-[#25D366]/30 border border-[#25D366]/50 text-[#25D366] text-xs font-bold transition shadow-lg shadow-[#25D366]/10"
                                >
                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2m.01 1.67c2.2 0 4.26.86 5.82 2.42a8.23 8.23 0 0 1 2.41 5.83c0 4.54-3.7 8.24-8.24 8.24-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.19 8.19 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.24-8.24m4.52 11.66c-.25-.13-1.47-.72-1.7-.81-.23-.08-.39-.13-.56.13-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.13-1.06-.39-2.02-1.25-.75-.67-1.26-1.5-1.41-1.75-.15-.25-.02-.39.11-.51.11-.11.25-.29.38-.44.12-.14.17-.25.25-.42.08-.17.04-.31-.02-.44-.06-.13-.56-1.35-.77-1.85-.2-.49-.41-.42-.56-.43h-.48c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.44 1.03 2.61.13.17 1.77 2.7 4.29 3.79.6.26 1.07.41 1.44.53.6.19 1.15.16 1.58.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.07.15-1.18-.06-.1-.21-.17-.46-.3z"/></svg>
                                    WhatsApp
                                </a>
                            @endif

                            <a href="#catalogo" class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-1.5 sm:py-2 rounded-2xl bg-[#FF3366] hover:bg-[#ff4d7a] text-white text-xs font-bold shadow-md shadow-[#FF3366]/30 transition">
                                🥟 Ver Menú
                            </a>

                            <button
                                type="button"
                                onclick="navigator.clipboard.writeText(window.location.href); alert('¡Enlace de la tienda copiado!');"
                                class="inline-flex items-center gap-1 px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-2xl bg-white/10 hover:bg-white/15 border border-white/15 text-slate-300 text-xs font-semibold transition"
                            >
                                🔗 Compartir
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta de Métricas y Sucursal -->
                @if ($activeBranch)
                    <div class="bg-[#121728]/90 backdrop-blur-xl rounded-3xl border border-white/15 p-6 shadow-2xl lg:min-w-[320px] space-y-3 text-xs text-white">
                        <div class="flex items-center justify-between">
                            <span class="font-display font-extrabold text-sm text-[#00F0FF]">{{ $activeBranch->branch_name }}</span>
                            <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-bold text-[10px]">Despacho Express</span>
                        </div>
                        <p class="text-slate-300">{{ $activeBranch->address }}</p>
                        
                        <div class="p-2.5 rounded-2xl bg-white/5 border border-white/10 space-y-1.5">
                            <div class="flex items-center justify-between text-slate-300">
                                <span>🕒 Horario Lun - Vie:</span>
                                <span class="font-bold text-white">7:30 am - 1:00 pm</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-300">
                                <span>🕒 Horario Sáb - Dom:</span>
                                <span class="font-bold text-white">7:30 am - 12:00 pm</span>
                            </div>
                        </div>

                        @if ($branchMetrics)
                            <div class="pt-2 border-t border-white/10 flex items-center justify-between font-bold">
                                <span class="text-slate-300">⏱️ ~{{ $branchMetrics['eta_minutes'] }} min ({{ number_format($branchMetrics['distance_km'], 1) }} km)</span>
                                <span class="text-[#FF3366] text-sm">Envío ${{ number_format($branchMetrics['delivery_fee'], 2) }}</span>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Selector de Sucursales si tiene más de 1 -->
            @if ($store->branches->count() > 1)
                <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-white/10">
                    <span class="text-xs font-semibold text-slate-300">Sucursal activa:</span>
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

    <!-- Banner Promocional Destacado de la Tienda (Si existe publicidad activa) -->
    @if ($storeAd)
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 relative z-10">
            <div class="relative overflow-hidden rounded-3xl border border-[#FF6600]/40 bg-gradient-to-r from-[#121728] via-[#1a1528] to-[#121728] p-5 sm:p-7 shadow-[0_12px_36px_-12px_rgba(255,102,0,0.35)] flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-5">
                    @if ($storeAd->image_url)
                        <img src="{{ $storeAd->image_url }}" alt="{{ $storeAd->headline }}" class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover ring-2 ring-[#FF6600]/50 shrink-0">
                    @endif
                    <div class="space-y-1">
                        <span class="px-2.5 py-0.5 rounded-full bg-[#FF6600]/20 border border-[#FF6600]/50 text-[#FF6600] text-[10px] font-extrabold tracking-wide uppercase">
                            {{ $storeAd->badge ?? 'Promoción Especial' }}
                        </span>
                        <h2 class="font-display text-lg sm:text-xl font-extrabold text-white">{{ $storeAd->headline }}</h2>
                        <p class="text-xs sm:text-sm text-slate-300 max-w-xl">{{ $storeAd->subline }}</p>
                    </div>
                </div>
                <a href="#catalogo" class="px-6 py-2.5 rounded-2xl bg-[#FF6600] hover:bg-[#ff771a] text-white text-xs font-bold shadow-lg shadow-[#FF6600]/30 transition shrink-0 cursor-pointer">
                    Aprovechar Promo
                </a>
            </div>
        </section>
    @endif

    <!-- Pilares de Calidad Artesanal Cuadrados -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 sm:pt-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-4">
            <div class="p-2.5 sm:p-4 rounded-2xl bg-[#121728]/80 border border-white/10 flex items-center gap-2.5 sm:gap-3">
                <span class="text-xl sm:text-2xl">🥟</span>
                <div>
                    <h4 class="font-bold text-[11px] sm:text-xs text-white">100% Hechos en Casa</h4>
                    <p class="text-[10px] sm:text-[11px] text-slate-400">Masa crujiente</p>
                </div>
            </div>
            <div class="p-2.5 sm:p-4 rounded-2xl bg-[#121728]/80 border border-white/10 flex items-center gap-2.5 sm:gap-3">
                <span class="text-xl sm:text-2xl">🥩</span>
                <div>
                    <h4 class="font-bold text-[11px] sm:text-xs text-white">Rellenos de Primera</h4>
                    <p class="text-[10px] sm:text-[11px] text-slate-400">Guiso criollo artesanal</p>
                </div>
            </div>
            <div class="p-2.5 sm:p-4 rounded-2xl bg-[#121728]/80 border border-white/10 flex items-center gap-2.5 sm:gap-3">
                <span class="text-xl sm:text-2xl">⚡</span>
                <div>
                    <h4 class="font-bold text-[11px] sm:text-xs text-white">Fritos al Momento</h4>
                    <p class="text-[10px] sm:text-[11px] text-slate-400">Calientes y dorados</p>
                </div>
            </div>
            <div class="p-2.5 sm:p-4 rounded-2xl bg-[#121728]/80 border border-white/10 flex items-center gap-2.5 sm:gap-3">
                <span class="text-xl sm:text-2xl">🛵</span>
                <div>
                    <h4 class="font-bold text-[11px] sm:text-xs text-white">Empaque Térmico</h4>
                    <p class="text-[10px] sm:text-[11px] text-slate-400">Mantiene el crujiente</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Catálogo de la Tienda -->
    <section id="catalogo" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10 space-y-4 sm:space-y-6">
        <!-- Barra de Búsqueda y Filtros de Categoría -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 sm:gap-4 p-3 sm:p-4 rounded-3xl bg-[#121728]/95 border border-white/10 shadow-xl">
            <!-- Tabs de categorías deslizables en móvil -->
            <div class="flex items-center gap-2 overflow-x-auto whitespace-nowrap no-scrollbar pb-1 sm:pb-0 sm:flex-wrap" id="categoryFilterTabs">
                <button
                    type="button"
                    data-filter="all"
                    class="tab-btn shrink-0 px-3.5 sm:px-4 py-1.5 sm:py-2 rounded-2xl text-xs font-bold transition bg-[#00F0FF] text-[#0B0E17] shadow-md shadow-[#00F0FF]/30 cursor-pointer"
                >
                    ✨ Todos ({{ $products->count() }})
                </button>
                <button
                    type="button"
                    data-filter="pastelitos"
                    class="tab-btn shrink-0 px-3.5 sm:px-4 py-1.5 sm:py-2 rounded-2xl text-xs font-bold transition bg-white/5 hover:bg-white/10 text-slate-300 border border-white/10 cursor-pointer"
                >
                    🥟 Pastelitos Caseros
                </button>
                <button
                    type="button"
                    data-filter="combos"
                    class="tab-btn shrink-0 px-3.5 sm:px-4 py-1.5 sm:py-2 rounded-2xl text-xs font-bold transition bg-white/5 hover:bg-white/10 text-slate-300 border border-white/10 cursor-pointer"
                >
                    🎁 Combos Ahorro
                </button>
                <button
                    type="button"
                    data-filter="bebidas"
                    class="tab-btn shrink-0 px-3.5 sm:px-4 py-1.5 sm:py-2 rounded-2xl text-xs font-bold transition bg-white/5 hover:bg-white/10 text-slate-300 border border-white/10 cursor-pointer"
                >
                    🥤 Bebidas & Café
                </button>
            </div>

            <!-- Buscador interno reactivo -->
            <div class="relative w-full md:w-72">
                <input
                    type="text"
                    id="menuSearchInput"
                    placeholder="Buscar pastel, combo, bebida..."
                    class="w-full pl-9 pr-4 py-2 rounded-2xl bg-[#0B0E17]/80 border border-white/15 text-xs text-white placeholder:text-slate-400 focus:outline-none focus:border-[#00F0FF]"
                >
                <span class="absolute left-3 top-2.5 text-slate-400 text-xs">🔍</span>
            </div>
        </div>

        <!-- Indicador de artículos visibles -->
        <div class="flex items-center justify-between text-xs text-slate-400 px-1">
            <span id="itemsCounterText">Mostrando {{ $products->count() }} artículos</span>
            <span class="text-[#00F0FF] font-semibold">Tasa referencial Bs. 2.000 / $1.00</span>
        </div>

        <!-- Grid de Productos -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2.5 sm:gap-5" id="productsGrid">
            @foreach ($products as $item)
                @php
                    $product = $item['product'];
                    $nameLower = mb_strtolower($product->name);
                    $skuLower = mb_strtolower($product->sku ?? '');
                    
                    // Categorización inteligente para filtrado
                    if (str_contains($skuLower, 'pas') || str_contains($nameLower, 'pastelito')) {
                        $itemCategory = 'pastelitos';
                    } elseif (str_contains($skuLower, 'cmb') || str_contains($nameLower, 'combo')) {
                        $itemCategory = 'combos';
                    } elseif (str_contains($skuLower, 'beb') || str_contains($nameLower, 'café') || str_contains($nameLower, 'malta') || str_contains($nameLower, 'pepsi') || str_contains($nameLower, 'jugo') || str_contains($nameLower, 'agua')) {
                        $itemCategory = 'bebidas';
                    } else {
                        $itemCategory = 'general';
                    }

                    // Precio en Bolívares referencial
                    $priceBs = number_format($item['price'] * 2000, 0, ',', '.');
                @endphp

                <article
                    class="product-card group bg-[#121728]/95 rounded-2xl sm:rounded-3xl border border-white/10 overflow-hidden shadow-lg hover:shadow-[0_18px_42px_-16px_rgba(255,102,0,0.35)] hover:border-[#FF6600]/60 hover:-translate-y-1.5 transition-all duration-300 flex flex-col"
                    data-category="{{ $itemCategory }}"
                    data-name="{{ $nameLower }}"
                    data-desc="{{ mb_strtolower($product->description) }}"
                >
                    <!-- Contenedor de Imagen -->
                    <div class="p-1.5 sm:p-2.5 pb-0">
                        <a href="{{ route('mall.products.show', [$store, $product]) }}" class="aspect-[4/3] rounded-xl sm:rounded-2xl bg-[#171E33] overflow-hidden block relative border border-white/10">
                            <img
                                src="{{ $product->image_url ?? '/images/catalog/desayuno.jpg' }}"
                                alt="{{ $product->name }}"
                                class="w-full h-full object-cover group-hover:scale-110 transition duration-700"
                                loading="lazy"
                            >
                            <div class="absolute inset-0 bg-gradient-to-t from-[#0B0E17]/80 via-transparent to-transparent"></div>
                            
                            <!-- Badges de Producto -->
                            @if ($product->badge)
                                <span class="absolute top-1.5 left-1.5 sm:top-2.5 sm:left-2.5 px-1.5 sm:px-2.5 py-0.5 sm:py-1 rounded-full bg-[#0B0E17]/90 backdrop-blur border border-[#FF6600]/60 text-white text-[9px] sm:text-[10px] font-bold shadow-sm">
                                    ✦ {{ $product->badge }}
                                </span>
                            @endif

                            @if ($itemCategory === 'combos')
                                <span class="absolute top-1.5 right-1.5 sm:top-2.5 sm:right-2.5 px-1.5 sm:px-2.5 py-0.5 sm:py-1 rounded-full bg-[#FF3366] text-white text-[9px] sm:text-[10px] font-extrabold shadow-sm">
                                    COMBO
                                </span>
                            @endif

                            @if ($product->prep_note)
                                <span class="absolute bottom-1.5 left-1.5 sm:bottom-2.5 sm:left-2.5 px-1.5 sm:px-2.5 py-0.5 rounded-full bg-[#0B0E17]/90 backdrop-blur border border-[#00F0FF]/40 text-[#00F0FF] text-[9px] sm:text-[10px] font-bold hidden xs:inline-flex">
                                    ⚡ {{ $product->prep_note }}
                                </span>
                            @endif
                        </a>
                    </div>

                    <!-- Detalles del Producto -->
                    <div class="p-2.5 sm:p-4 flex-1 flex flex-col justify-between gap-2 sm:gap-3">
                        <div class="space-y-1">
                            <a href="{{ route('mall.products.show', [$store, $product]) }}" class="font-display font-bold text-white group-hover:text-[#00F0FF] transition block text-xs sm:text-base leading-tight line-clamp-2">
                                {{ $product->name }}
                            </a>
                            <p class="text-[11px] sm:text-xs text-slate-400 line-clamp-1 sm:line-clamp-2 leading-relaxed">{{ $product->description }}</p>
                        </div>

                        <!-- Precios y Botón de Compra -->
                        <div class="pt-2 sm:pt-3 border-t border-white/10 flex flex-col sm:flex-row sm:items-end justify-between gap-2">
                            <div>
                                <span class="block text-base sm:text-2xl font-black text-white leading-tight">
                                    ${{ number_format($item['price'], 2) }}
                                </span>
                                <span class="block text-[10px] sm:text-xs font-bold text-[#00F0FF]">
                                    Bs. {{ $priceBs }}
                                </span>
                                <span class="hidden sm:block text-[10px] text-slate-400 mt-0.5">
                                    Stock: {{ $item['stock'] }} disponibles
                                </span>
                            </div>

                            @if ($activeBranch && $item['is_available'])
                                <form action="{{ route('cart.add') }}" method="POST" data-ajax-cart class="flex items-center gap-1">
                                    @csrf
                                    <input type="hidden" name="branch_id" value="{{ $activeBranch->id }}">
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    
                                    <!-- Selector de Cantidad Rápida -->
                                    <div class="flex items-center bg-[#0B0E17] border border-white/15 rounded-lg sm:rounded-xl overflow-hidden shrink-0">
                                        <button type="button" class="qty-btn px-1.5 sm:px-2 py-0.5 sm:py-1 text-slate-300 hover:text-white font-bold text-xs" onclick="const input = this.parentElement.querySelector('input'); if(input.value > 1) input.value--;">-</button>
                                        <input type="number" name="quantity" value="1" min="1" max="{{ $item['stock'] }}" class="w-6 sm:w-8 text-center bg-transparent text-[11px] sm:text-xs font-bold text-white focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                        <button type="button" class="qty-btn px-1.5 sm:px-2 py-0.5 sm:py-1 text-slate-300 hover:text-white font-bold text-xs" onclick="const input = this.parentElement.querySelector('input'); if(input.value < {{ $item['stock'] }}) input.value++;">+</button>
                                    </div>

                                    <button
                                        type="submit"
                                        class="flex-1 sm:flex-none px-2 sm:px-3.5 py-1 sm:py-2 rounded-lg sm:rounded-xl bg-[#FF3366] hover:bg-[#ff4d7a] text-white text-[11px] sm:text-xs font-bold shadow-md shadow-[#FF3366]/30 transition cursor-pointer flex items-center justify-center gap-1 shrink-0"
                                    >
                                        <span>+ Pedir</span>
                                    </button>
                                </form>
                            @else
                                <span class="px-2 py-1 rounded-lg bg-white/5 border border-white/10 text-slate-400 text-[10px] sm:text-xs font-semibold text-center">
                                    Agotado
                                </span>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <!-- Mensaje de Búsqueda Vacía -->
        <div id="noResultsMessage" class="hidden py-16 text-center space-y-3">
            <span class="text-4xl">🥟</span>
            <h3 class="text-lg font-bold text-white">No encontramos productos con ese término</h3>
            <p class="text-xs text-slate-400">Intenta buscar por "carne", "pollo", "combo", "malta" o restablece el filtro.</p>
            <button type="button" onclick="resetFilters()" class="px-4 py-2 rounded-xl bg-white/10 text-xs font-bold text-[#00F0FF] hover:bg-white/15 transition cursor-pointer">
                Ver todo el menú
            </button>
        </div>
    </section>

    <!-- Script Reactivo para Filtros y Búsqueda Inmediata -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tabs = document.querySelectorAll('#categoryFilterTabs .tab-btn');
            const searchInput = document.getElementById('menuSearchInput');
            const cards = document.querySelectorAll('.product-card');
            const counterText = document.getElementById('itemsCounterText');
            const noResults = document.getElementById('noResultsMessage');

            let currentCategory = 'all';
            let currentSearch = '';

            function updateView() {
                let visibleCount = 0;

                cards.forEach(card => {
                    const category = card.getAttribute('data-category');
                    const name = card.getAttribute('data-name') || '';
                    const desc = card.getAttribute('data-desc') || '';

                    const matchesCategory = (currentCategory === 'all' || category === currentCategory);
                    const matchesSearch = !currentSearch || name.includes(currentSearch) || desc.includes(currentSearch);

                    if (matchesCategory && matchesSearch) {
                        card.classList.remove('hidden');
                        visibleCount++;
                    } else {
                        card.classList.add('hidden');
                    }
                });

                if (counterText) {
                    counterText.textContent = `Mostrando ${visibleCount} de ${cards.length} artículos`;
                }

                if (noResults) {
                    if (visibleCount === 0) {
                        noResults.classList.remove('hidden');
                    } else {
                        noResults.classList.add('hidden');
                    }
                }
            }

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    tabs.forEach(t => {
                        t.className = 'tab-btn px-4 py-2 rounded-2xl text-xs font-bold transition bg-white/5 hover:bg-white/10 text-slate-300 border border-white/10 cursor-pointer';
                    });
                    tab.className = 'tab-btn px-4 py-2 rounded-2xl text-xs font-bold transition bg-[#00F0FF] text-[#0B0E17] shadow-md shadow-[#00F0FF]/30 cursor-pointer';
                    currentCategory = tab.getAttribute('data-filter');
                    updateView();
                });
            });

            if (searchInput) {
                searchInput.addEventListener('input', (e) => {
                    currentSearch = e.target.value.trim().toLowerCase();
                    updateView();
                });
            }

            window.resetFilters = function() {
                if (searchInput) searchInput.value = '';
                currentSearch = '';
                currentCategory = 'all';
                tabs.forEach((t, idx) => {
                    if (idx === 0) {
                        t.className = 'tab-btn px-4 py-2 rounded-2xl text-xs font-bold transition bg-[#00F0FF] text-[#0B0E17] shadow-md shadow-[#00F0FF]/30 cursor-pointer';
                    } else {
                        t.className = 'tab-btn px-4 py-2 rounded-2xl text-xs font-bold transition bg-white/5 hover:bg-white/10 text-slate-300 border border-white/10 cursor-pointer';
                    }
                });
                updateView();
            };
        });
    </script>
</x-layouts.mall>
