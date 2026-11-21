<x-layouts.mall title="JIHOVA · El Primer Centro Comercial Online de Venezuela">
    <!-- Hero Editorial Futurista con Carrusel Dinámico -->
    <section class="relative overflow-hidden border-b border-white/10">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(108,40,254,0.38),transparent_48%),radial-gradient(circle_at_bottom_left,rgba(0,240,255,0.22),transparent_52%)]"></div>
        <div class="absolute inset-0 bg-[linear-gradient(to_right,rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:36px_36px]"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16 grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
            <!-- Columna Izquierda: Posicionamiento + Buscador + Ubicación -->
            <div class="lg:col-span-6 space-y-6">
                <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-[#121728]/90 backdrop-blur-md border border-[#00F0FF]/35 text-xs font-bold text-[#00F0FF] shadow-[0_0_24px_-6px_rgba(0,240,255,0.45)]">
                    <span class="w-2 h-2 rounded-full bg-[#00F0FF] animate-ping"></span>
                    EL PRIMER CENTRO COMERCIAL ONLINE DE VENEZUELA
                </div>

                <h1 class="font-display text-3xl sm:text-5xl lg:text-[3.25rem] font-extrabold tracking-tight leading-[1.05] text-[#F3F4F6]">
                    Conectividad comercial <span class="jihova-gradient-text">sin límites</span>, directo a tu puerta.
                </h1>

                <p class="text-base sm:text-lg text-slate-300 max-w-xl leading-relaxed">
                    Desde un desayuno caliente hasta un par de zapatos: recorre {{ $stores->count() }} tiendas verificadas, paga en un solo checkout y sigue tu delivery en tiempo real.
                </p>

                <!-- Buscador Hero -->
                <form action="{{ route('mall.explore') }}" method="GET" class="flex flex-col sm:flex-row gap-2.5 p-1.5 rounded-3xl bg-[#121728]/90 backdrop-blur-xl border border-white/15 shadow-2xl max-w-xl focus-within:border-[#00F0FF]/60 transition">
                    <input
                        type="search"
                        name="q"
                        placeholder="Ej: desayuno criollo, zapatos running, audífonos..."
                        class="flex-1 px-4 py-3 rounded-2xl bg-[#0B0E17]/80 text-[#F3F4F6] placeholder:text-slate-400 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#00F0FF]/40"
                    >
                    <button type="submit" class="px-6 py-3 rounded-2xl bg-[#FF3366] hover:bg-[#ff4d7a] text-white font-bold text-sm shadow-lg shadow-[#FF3366]/35 transition cursor-pointer shrink-0">
                        Explorar Tiendas →
                    </button>
                </form>

                <!-- Chips rápidos -->
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="text-slate-400 font-semibold">Tendencias:</span>
                    @foreach ([
                        'Desayuno' => '🥐 Desayuno',
                        'Zapatos' => '👟 Zapatos',
                        'Café' => '☕ Café',
                        'Audífonos' => '🎧 Audífonos',
                        'Frutas' => '🍓 Frutas',
                        'Vitamina' => '💊 Bienestar',
                    ] as $term => $label)
                        <a href="{{ route('mall.explore', ['q' => $term]) }}" class="px-3 py-1.5 rounded-full bg-[#121728]/85 hover:bg-[#6C28FE]/30 border border-white/10 hover:border-[#00F0FF]/45 text-slate-200 transition">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                <!-- Barra de Ubicación y Métricas -->
                <div class="pt-2 flex flex-wrap items-center gap-3">
                    <button type="button" data-open-location-modal class="inline-flex items-center gap-2.5 px-4 py-2.5 rounded-2xl bg-[#121728]/90 hover:border-[#00F0FF]/60 border border-white/15 text-left transition cursor-pointer">
                        <span class="text-[#00F0FF] text-base">📍</span>
                        <div class="text-xs">
                            <span class="block text-slate-400">Entregar en</span>
                            <span class="font-bold text-white truncate max-w-[200px] block">{{ $activeLocation['label'] }}</span>
                        </div>
                        <span class="text-xs text-[#00F0FF] font-bold ml-1">Cambiar</span>
                    </button>

                    <div class="flex items-center gap-4 px-4 py-2.5 rounded-2xl bg-[#121728]/90 border border-white/15 text-xs">
                        <div>
                            <span class="block font-display font-bold text-base text-[#00F0FF]">{{ $stores->where('open_branches_count', '>', 0)->count() }}</span>
                            <span class="text-slate-400">Abiertas</span>
                        </div>
                        <div class="w-px h-7 bg-white/10"></div>
                        <div>
                            <span class="block font-display font-bold text-base text-white">&lt; 45 min</span>
                            <span class="text-slate-400">Entrega</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Banner con Slides Interactivo -->
            <div class="lg:col-span-6 space-y-3">
                @php
                    $allSlides = collect();

                    foreach ($heroAdBanners as $ad) {
                        $allSlides->push([
                            'image' => $ad->image_url,
                            'badge' => '✦ ' . ($ad->store->business_name ?? 'Patrocinado'),
                            'title' => $ad->title,
                            'sub' => $ad->subtitle ?? 'Disponible para compra y delivery inmediato.',
                            'tag' => $ad->cta_text ?? 'Comprar Ahora',
                            'url' => route('mall.ads.click', $ad),
                            'tab' => \Illuminate\Support\Str::limit($ad->title, 14),
                            'is_sponsored' => true,
                        ]);
                    }

                    $defaultSpotlights = [
                        [
                            'image' => '/images/catalog/hero-mall.jpg',
                            'badge' => 'Experiencia Multi-Tienda',
                            'title' => 'Tu centro comercial completo en un solo carrito',
                            'sub' => 'Combina gastronomía, calzado y tecnología con tracking GPS en vivo.',
                            'tag' => 'Envío coordinado',
                            'url' => route('mall.explore'),
                            'tab' => 'Todo JIHOVA',
                            'is_sponsored' => false,
                        ],
                        [
                            'image' => '/images/catalog/desayuno.jpg',
                            'badge' => 'Aurora Café · 12 min',
                            'title' => 'Desayuno Criollo Completo recién hecho',
                            'sub' => 'Huevos al gusto, aguacate, pan artesanal y café caliente a domicilio.',
                            'tag' => 'Desde $9.50',
                            'url' => route('mall.explore', ['q' => 'Desayuno']),
                            'tab' => 'Desayunos',
                            'is_sponsored' => false,
                        ],
                        [
                            'image' => '/images/catalog/tenis.jpg',
                            'badge' => 'StepUp Calzado · Top Ventas',
                            'title' => 'Zapatos Running AirPulse & Cuero Urbano',
                            'sub' => 'Recibe tus tenis o calzado formal hoy mismo en la puerta de tu casa.',
                            'tag' => 'Desde $59.50',
                            'url' => route('mall.explore', ['q' => 'Zapatos']),
                            'tab' => 'Calzado',
                            'is_sponsored' => false,
                        ],
                        [
                            'image' => '/images/catalog/tecnologia.jpg',
                            'badge' => 'VoltTech Store · Garantía 1 año',
                            'title' => 'Audio ANC Studio & Smartwatches GPS',
                            'sub' => 'Tecnología original sellada de fábrica con despacho asegurado.',
                            'tag' => 'Desde $38.00',
                            'url' => route('mall.explore', ['category' => 'tecnologia']),
                            'tab' => 'Tecnología',
                            'is_sponsored' => false,
                        ],
                    ];

                    foreach ($defaultSpotlights as $ds) {
                        if ($allSlides->count() < 5) {
                            $allSlides->push($ds);
                        }
                    }
                @endphp

                <div class="relative aspect-[16/11] rounded-[2rem] overflow-hidden border border-[#00F0FF]/30 shadow-[0_24px_60px_-20px_rgba(108,40,254,0.55)] bg-[#121728]">
                    @foreach ($allSlides as $idx => $slide)
                        <a
                            href="{{ $slide['url'] }}"
                            data-hero-slide
                            class="absolute inset-0 transition-opacity duration-700 {{ $idx === 0 ? 'opacity-100 z-10' : 'opacity-0 pointer-events-none' }}"
                        >
                            <img src="{{ $slide['image'] }}" alt="{{ $slide['title'] }}" class="w-full h-full object-cover animate-ken-burns">
                            <div class="absolute inset-0 bg-gradient-to-t from-[#0B0E17] via-[#0B0E17]/35 to-transparent"></div>

                            <div class="absolute top-4 left-4 right-4 flex items-center justify-between gap-2">
                                <span class="px-3 py-1 rounded-full bg-[#0B0E17]/85 backdrop-blur-md border border-[#00F0FF]/40 text-xs font-bold text-[#00F0FF] flex items-center gap-1.5">
                                    @if ($slide['is_sponsored'] ?? false)
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#00F0FF] animate-pulse"></span>
                                    @endif
                                    {{ $slide['badge'] }}
                                </span>
                                <span class="px-3.5 py-1 rounded-full bg-[#FF3366] text-xs font-extrabold text-white shadow-lg shadow-[#FF3366]/40 animate-float-slow">
                                    {{ $slide['tag'] }}
                                </span>
                            </div>

                            <div class="absolute bottom-5 left-5 right-5 space-y-1">
                                <h2 class="font-display text-xl sm:text-2xl font-bold text-white leading-tight">
                                    {{ $slide['title'] }}
                                </h2>
                                <p class="text-xs sm:text-sm text-slate-200 line-clamp-2">
                                    {{ $slide['sub'] }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>

                <!-- Controles del Carrusel -->
                <div class="grid grid-cols-{{ min($allSlides->count(), 5) }} gap-2">
                    @foreach ($allSlides as $idx => $slide)
                        <button
                            type="button"
                            data-hero-dot
                            class="px-2.5 py-2 rounded-xl text-xs font-bold transition cursor-pointer truncate border {{ $idx === 0 ? 'bg-[#FF3366] text-white border-[#FF3366] shadow-lg' : 'bg-[#121728]/80 text-slate-300 border-white/10 hover:border-[#00F0FF]/40' }}"
                        >
                            {{ $slide['tab'] }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- Vitrina Luminosa del Mall: Transición a Blanco Alabastro (#F8FAFC) con Tarjetas Blancas (#FFFFFF) -->
    <div class="bg-[#F8FAFC] text-slate-800 relative z-10 transition-colors duration-300 rounded-t-[2.5rem] sm:rounded-t-[3.5rem] shadow-[0_-20px_50px_rgba(0,0,0,0.5)] border-t border-slate-200/90 pt-8 sm:pt-14 pb-16 space-y-12 sm:space-y-16">
        <!-- Rubros / Categorías Visuales (Swipe Horizontal en Móvil) -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-5">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-[#6C28FE]">Departamentos del Mall</p>
                    <h2 class="font-display text-xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Explora por categoría</h2>
                </div>
                <a href="{{ route('mall.explore') }}" class="text-xs sm:text-sm font-bold text-[#6C28FE] hover:text-[#FF3366] transition">Ver catálogo →</a>
            </div>

            <div class="flex overflow-x-auto gap-3 pb-2 no-scrollbar px-0.5 sm:grid sm:grid-cols-3 lg:grid-cols-5">
                @php
                    $categoryPhotos = [
                        'desayunos-cafe' => ['/images/catalog/desayuno.jpg', '☕'],
                        'calzado-moda' => ['/images/catalog/tenis.jpg', '👟'],
                        'tecnologia' => ['/images/catalog/tecnologia.jpg', '⚡'],
                        'farmacia-salud' => ['/images/catalog/farmacia.jpg', '💊'],
                        'supermercado-hogar' => ['/images/catalog/supermercado.jpg', '🛒'],
                    ];
                @endphp
                @foreach ($categories as $category)
                    @php
                        $meta = $categoryPhotos[$category->slug] ?? ['/images/catalog/hero-mall.jpg', '✨'];
                    @endphp
                    <a href="{{ route('mall.explore', ['category' => $category->slug]) }}" class="group min-w-[130px] sm:min-w-0 shrink-0 sm:shrink bg-white rounded-2xl sm:rounded-3xl border border-slate-200/90 p-2 sm:p-3 hover:border-[#6C28FE]/50 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.06)] hover:shadow-[0_14px_34px_-10px_rgba(108,40,254,0.18)] hover:-translate-y-1 transition-all duration-300 flex flex-col gap-2 sm:gap-3">
                        <div class="aspect-[16/10] rounded-xl sm:rounded-2xl overflow-hidden bg-slate-100 relative border border-slate-200/70">
                            <img
                                src="{{ $meta[0] }}"
                                alt="{{ $category->name }}"
                                class="w-full h-full object-cover group-hover:scale-110 transition duration-500"
                            >
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/65 to-transparent"></div>
                            <span class="absolute bottom-1.5 left-1.5 sm:bottom-2 sm:left-2 w-6 h-6 sm:w-8 sm:h-8 rounded-lg sm:rounded-xl bg-slate-900/80 backdrop-blur border border-white/20 flex items-center justify-center text-xs sm:text-sm text-white">
                                {{ $meta[1] }}
                            </span>
                        </div>
                        <div class="px-1 pb-1">
                            <h3 class="font-display font-bold text-xs sm:text-sm text-slate-900 group-hover:text-[#6C28FE] transition truncate">{{ $category->name }}</h3>
                            <p class="hidden sm:block text-xs text-slate-500 line-clamp-2 mt-0.5">{{ $category->description }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        <!-- Vitrinas & Anuncios Patrocinados (Swipe Horizontal en Móvil) -->
        @if ($vitrinaAdBanners->isNotEmpty())
            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-100 border border-violet-200 text-xs font-bold text-[#6C28FE] mb-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#6C28FE] animate-pulse"></span>
                            Vitrinas Comerciales JIHOVA
                        </div>
                        <h2 class="font-display text-xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Vitrinas & Anuncios</h2>
                    </div>
                    <span class="text-xs text-slate-500 font-medium hidden sm:inline">Publicidad certificada</span>
                </div>

                <div class="flex overflow-x-auto gap-4 no-scrollbar pb-2 sm:grid sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($vitrinaAdBanners as $ad)
                        <article class="group relative min-w-[280px] sm:min-w-0 shrink-0 sm:shrink rounded-2xl sm:rounded-3xl bg-white border border-slate-200/90 overflow-hidden shadow-[0_4px_20px_-4px_rgba(0,0,0,0.06)] hover:border-[#6C28FE]/40 hover:shadow-xl transition-all duration-300 flex flex-col">
                            <div class="relative aspect-[16/9] w-full overflow-hidden bg-slate-100">
                                <img src="{{ $ad->image_url }}" alt="{{ $ad->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/75 via-transparent to-transparent"></div>
                                <span class="absolute top-2.5 left-2.5 sm:top-3 sm:left-3 px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full bg-slate-900/80 backdrop-blur border border-white/20 text-[10px] sm:text-[11px] font-bold text-[#00F0FF]">
                                    ✦ {{ $ad->store->business_name }}
                                </span>
                            </div>
                            <div class="p-4 sm:p-5 flex-1 flex flex-col justify-between space-y-3 sm:space-y-4">
                                <div>
                                    <h3 class="font-display text-sm sm:text-lg font-bold text-slate-900 group-hover:text-[#6C28FE] transition leading-snug line-clamp-2">
                                        {{ $ad->title }}
                                    </h3>
                                    @if ($ad->subtitle)
                                        <p class="text-xs text-slate-600 mt-1 line-clamp-2">{{ $ad->subtitle }}</p>
                                    @endif
                                </div>
                                <div class="pt-2 flex items-center justify-between border-t border-slate-100">
                                    <span class="text-xs font-semibold text-slate-700">{{ $ad->product ? '$' . number_format((float) $ad->product->base_price, 2) : 'Tienda Oficial' }}</span>
                                    <a href="{{ route('mall.ads.click', $ad) }}" class="inline-flex items-center gap-1 px-3 py-1.5 sm:px-4 sm:py-2 rounded-xl bg-gradient-to-r from-[#6C28FE] to-[#FF3366] text-white text-[11px] sm:text-xs font-extrabold shadow-md hover:opacity-95 transition">
                                        {{ $ad->cta_text ?? 'Ver Oferta' }} →
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Tiendas del Centro Comercial (Scroll Horizontal en Móvil) -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-5">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-[#6C28FE]">Tiendas Verificadas</p>
                    <h2 class="font-display text-xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Locales abiertos</h2>
                </div>
                <a href="{{ route('mall.stores') }}" class="text-xs sm:text-sm font-bold text-[#6C28FE] hover:text-[#FF3366] transition">Directorio completo →</a>
            </div>

            <div class="flex overflow-x-auto gap-3.5 no-scrollbar pb-3 sm:grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($stores->take(8) as $storeItem)
                    <x-store-card :item="$storeItem" :theme="'light'" />
                @endforeach
            </div>
        </section>

        <!-- Vitrina JIHOVA: Digitaliza tu vitrina -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-r from-[#23145C] via-[#1B1F4A] to-[#0B2C42] border border-[#00F0FF]/30 text-white p-6 sm:p-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center shadow-[0_24px_64px_-24px_rgba(108,40,254,0.6)]">
                <div class="pointer-events-none absolute -top-24 -right-24 w-72 h-72 rounded-full bg-[#00F0FF]/20 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-24 left-12 w-72 h-72 rounded-full bg-[#6C28FE]/30 blur-3xl"></div>

                <div class="relative lg:col-span-7 space-y-4">
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#00F0FF]/15 border border-[#00F0FF]/40 text-[#00F0FF] text-xs font-bold">
                        ⚡ Conectividad comercial · Multi-Tienda
                    </span>
                    <h2 class="font-display text-2xl sm:text-4xl font-extrabold leading-tight">
                        Digitaliza tu vitrina o compra todo en un solo carrito.
                    </h2>
                    <p class="text-sm sm:text-base text-slate-200 max-w-xl leading-relaxed">
                        Pide un desayuno de Aurora Café y unos tenis de StepUp en la misma compra, o suma tu comercio a JIHOVA para vender con nuestra flota propia en tiempo real.
                    </p>
                    <div class="flex flex-wrap gap-3 pt-2">
                        <a href="{{ route('mall.explore') }}" class="px-6 py-3 rounded-2xl bg-[#FF3366] hover:bg-[#ff4d7a] text-white font-bold text-sm shadow-lg shadow-[#FF3366]/35 transition">
                            Explorar Tiendas
                        </a>
                        <a href="{{ route('login') }}" class="px-6 py-3 rounded-2xl bg-[#0B0E17]/60 hover:bg-[#0B0E17]/90 border border-[#00F0FF]/45 text-[#00F0FF] font-bold text-sm transition">
                            Probar roles en vivo
                        </a>
                    </div>
                </div>

                <div class="relative lg:col-span-5 grid grid-cols-2 gap-4 items-center">
                    <div class="col-span-2 rounded-3xl overflow-hidden border border-white/15 bg-[#0B0E17]/80 p-3 flex items-center gap-4 shadow-xl">
                        <img src="/images/catalog/jihova-brand-logo.jpg" alt="Identidad JIHOVA" class="w-20 h-20 rounded-2xl object-cover shrink-0 border border-white/10">
                        <div class="min-w-0">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-[#00F0FF]">Identidad Oficial</p>
                            <p class="font-display font-bold text-white text-base">Conectividad sin límites</p>
                            <p class="text-xs text-slate-300 mt-0.5">Un solo pago · Sub-órdenes por tienda</p>
                        </div>
                    </div>
                    <a href="{{ route('mall.explore', ['q' => 'Desayuno']) }}" class="group bg-[#0B0E17]/70 backdrop-blur-md border border-white/15 hover:border-[#00F0FF]/55 rounded-3xl p-3 space-y-2 hover:-translate-y-1 transition">
                        <img src="/images/catalog/desayuno.jpg" alt="Desayunos" class="w-full aspect-square object-cover rounded-2xl">
                        <div class="px-1">
                            <p class="text-[11px] font-bold text-[#FF3366]">Gastronomía</p>
                            <p class="text-sm font-bold text-white truncate">Desayunos</p>
                        </div>
                    </a>
                    <a href="{{ route('mall.explore', ['q' => 'Zapatos']) }}" class="group bg-[#0B0E17]/70 backdrop-blur-md border border-white/15 hover:border-[#6C28FE]/70 rounded-3xl p-3 space-y-2 hover:-translate-y-1 transition">
                        <img src="/images/catalog/zapatos.jpg" alt="Calzado" class="w-full aspect-square object-cover rounded-2xl">
                        <div class="px-1">
                            <p class="text-[11px] font-bold text-[#00F0FF]">Calzado</p>
                            <p class="text-sm font-bold text-white truncate">Zapatos &amp; Tenis</p>
                        </div>
                    </a>
                </div>
            </div>
        </section>

        <!-- Catálogo Destacado Multi-Rubro -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-[#FF3366]">Selección JIHOVA</p>
                    <h2 class="font-display text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Destacados para pedir ahora</h2>
                </div>
                <a href="{{ route('mall.explore') }}" class="text-sm font-bold text-[#6C28FE] hover:text-[#FF3366] transition">Explorar todo →</a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-5">
                @foreach ($featuredOffers as $offer)
                    <x-product-card :offer="$offer" :theme="'light'" />
                @endforeach
            </div>
        </section>

        <!-- Cómo funciona el delivery gestionado por la página -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-10 grid grid-cols-1 md:grid-cols-3 gap-8 shadow-sm">
                <div class="space-y-2.5">
                    <span class="w-11 h-11 rounded-2xl bg-gradient-to-br from-[#6C28FE] to-violet-500 text-white font-display font-bold flex items-center justify-center shadow-lg shadow-[#6C28FE]/25">1</span>
                    <h3 class="font-display font-bold text-slate-900 text-base">Compra en múltiples tiendas de JIHOVA</h3>
                    <p class="text-sm text-slate-600">Mezcla tu desayuno de la mañana con unos tenis nuevos o medicinas en un solo carrito.</p>
                </div>
                <div class="space-y-2.5">
                    <span class="w-11 h-11 rounded-2xl bg-gradient-to-br from-[#00F0FF] to-cyan-500 text-[#0B0E17] font-display font-extrabold flex items-center justify-center shadow-lg shadow-[#00F0FF]/25">2</span>
                    <h3 class="font-display font-bold text-slate-900 text-base">Pago unificado y transparente</h3>
                    <p class="text-sm text-slate-600">El sistema divide tu compra en sub-pedidos por cada tienda de forma automática y segura.</p>
                </div>
                <div class="space-y-2.5">
                    <span class="w-11 h-11 rounded-2xl bg-gradient-to-br from-[#FF3366] to-rose-500 text-white font-display font-bold flex items-center justify-center shadow-lg shadow-[#FF3366]/25">3</span>
                    <h3 class="font-display font-bold text-slate-900 text-base">Delivery propio con seguimiento</h3>
                    <p class="text-sm text-slate-600">Nuestros repartidores retiran en cada tienda y te llevan todo a casa con rastreo paso a paso.</p>
                </div>
            </div>
        </section>
    </div>
</x-layouts.mall>
