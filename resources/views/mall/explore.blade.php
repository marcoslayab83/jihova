<x-layouts.mall title="Explorar Centro Comercial · JIHOVA">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-[#00F0FF]">Catálogo Hiperlocal</p>
                <h1 class="font-display text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                    @if ($filters['q'] !== '')
                        Resultados para "{{ $filters['q'] }}"
                    @else
                        Explora todo el Centro Comercial
                    @endif
                </h1>
                <p class="text-sm text-slate-400">Mostrando tiendas y productos calculados hacia <span class="text-white font-semibold">{{ $activeLocation['label'] }}</span>.</p>
            </div>
            <span class="text-xs font-bold px-3.5 py-1.5 rounded-full bg-[#121728] border border-white/15 text-[#00F0FF] self-start">
                {{ $offers->count() }} productos · {{ $stores->count() }} tiendas
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8">
            <!-- Sidebar Filtros -->
            <aside class="lg:col-span-3">
                <details class="bg-[#121728]/95 backdrop-blur-md rounded-3xl border border-white/10 p-4 sm:p-5 sticky top-24 shadow-xl group" open>
                    <summary class="lg:hidden flex items-center justify-between font-bold text-xs text-white cursor-pointer select-none pb-2 border-b border-white/10">
                        <span class="flex items-center gap-2 text-[#00F0FF]">
                            <span>⚙️</span>
                            <span>Filtros y Búsqueda</span>
                            @if ($filters['q'] || $filters['category'] || $filters['max_price'])
                                <span class="px-2 py-0.5 rounded-full bg-[#FF3366] text-white text-[10px]">Activos</span>
                            @endif
                        </span>
                        <span class="text-xs text-slate-400 group-open:rotate-180 transition-transform">▼</span>
                    </summary>
                    <form action="{{ route('mall.explore') }}" method="GET" class="pt-3 lg:pt-0 space-y-4 sm:space-y-5">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-300">Búsqueda</label>
                        <input
                            type="search"
                            name="q"
                            value="{{ $filters['q'] }}"
                            placeholder="Ej. desayuno, zapatos, café..."
                            class="w-full px-3.5 py-2.5 rounded-xl border border-white/15 bg-[#0B0E17] text-sm text-white placeholder:text-slate-500 focus:outline-none focus:border-[#00F0FF]"
                        >
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-300">Rubro / Categoría</label>
                        <select name="category" class="w-full px-3.5 py-2.5 rounded-xl border border-white/15 bg-[#0B0E17] text-sm text-white focus:outline-none focus:border-[#00F0FF]">
                            <option value="">Todos los rubros</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->slug }}" @selected($filters['category'] === $cat->slug)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-300">Precio máximo ($)</label>
                        <input
                            type="number"
                            step="0.50"
                            min="1"
                            name="max_price"
                            value="{{ $filters['max_price'] }}"
                            placeholder="Sin límite"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-white/15 bg-[#0B0E17] text-sm text-white placeholder:text-slate-500 focus:outline-none focus:border-[#00F0FF]"
                        >
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-300">Ordenar por</label>
                        <select name="sort" class="w-full px-3.5 py-2.5 rounded-xl border border-white/15 bg-[#0B0E17] text-sm text-white focus:outline-none focus:border-[#00F0FF]">
                            <option value="relevance" @selected($filters['sort'] === 'relevance')>Más cercanos y rápidos</option>
                            <option value="fastest" @selected($filters['sort'] === 'fastest')>Menor tiempo de entrega</option>
                            <option value="price_asc" @selected($filters['sort'] === 'price_asc')>Precio: menor a mayor</option>
                            <option value="price_desc" @selected($filters['sort'] === 'price_desc')>Precio: mayor a menor</option>
                            <option value="rating" @selected($filters['sort'] === 'rating')>Mejor valoradas</option>
                        </select>
                    </div>

                    <div class="space-y-2.5 pt-2 border-t border-white/10">
                        <label class="flex items-center gap-2.5 text-sm text-slate-200 cursor-pointer">
                            <input type="checkbox" name="open_only" value="1" @checked($filters['open_only']) class="rounded border-white/25 bg-[#0B0E17] accent-[#FF3366]">
                            <span>Solo tiendas abiertas</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-sm text-slate-200 cursor-pointer">
                            <input type="checkbox" name="within_radius" value="1" @checked($filters['within_radius']) class="rounded border-white/25 bg-[#0B0E17] accent-[#00F0FF]">
                            <span>Dentro de mi radio de delivery</span>
                        </label>
                    </div>

                        <div class="flex gap-2">
                            <button type="submit" class="flex-1 py-2.5 rounded-xl bg-[#FF3366] hover:bg-[#ff4d7a] text-white text-sm font-bold shadow-md shadow-[#FF3366]/25 transition cursor-pointer">
                                Aplicar filtros
                            </button>
                            <a href="{{ route('mall.explore') }}" class="px-3.5 py-2.5 rounded-xl border border-white/15 bg-white/5 text-sm font-semibold text-slate-300 hover:text-white hover:bg-white/10">
                                Limpiar
                            </a>
                        </div>
                    </form>
                </details>
            </aside>

            <!-- Resultados -->
            <div class="lg:col-span-9 space-y-8">
                @if ($stores->isNotEmpty())
                    <div class="space-y-3">
                        <h2 class="font-display text-lg font-bold text-white">Tiendas coincidentes</h2>
                        <div class="flex overflow-x-auto gap-3.5 no-scrollbar pb-2 sm:grid sm:grid-cols-2 xl:grid-cols-3 sm:gap-4">
                            @foreach ($stores->take(6) as $storeItem)
                                <x-store-card :item="$storeItem" />
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="space-y-3">
                    <h2 class="font-display text-lg font-bold text-white">Artículos disponibles</h2>

                    @if ($offers->isEmpty())
                        <div class="bg-[#121728] rounded-3xl border border-white/10 p-12 text-center space-y-3">
                            <p class="text-3xl">🔍</p>
                            <h3 class="font-display text-lg font-bold text-white">No encontramos artículos con esos filtros</h3>
                            <p class="text-sm text-slate-400">Prueba buscando "desayuno", "zapatos", "café" o quitando el filtro de precio máximo.</p>
                            <a href="{{ route('mall.explore') }}" class="inline-flex px-5 py-2.5 rounded-xl bg-[#FF3366] hover:bg-[#ff4d7a] text-white text-sm font-bold transition">
                                Ver todo el catálogo
                            </a>
                        </div>
                    @else
                        <div class="grid grid-cols-2 sm:grid-cols-2 xl:grid-cols-3 gap-3 sm:gap-5">
                            @foreach ($offers as $offer)
                                <x-product-card :offer="$offer" />
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.mall>
