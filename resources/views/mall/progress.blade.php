<x-layouts.mall title="Progreso del Proyecto · JIHOVA">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <div class="relative overflow-hidden bg-gradient-to-r from-[#23145C] via-[#151A38] to-[#0B2942] border border-[#00F0FF]/30 text-white rounded-3xl p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-6 shadow-2xl">
            <div class="space-y-1.5">
                <span class="px-3 py-1 rounded-full bg-[#00F0FF]/15 border border-[#00F0FF]/40 text-[#00F0FF] text-xs font-bold">100% MVP Completado</span>
                <h1 class="font-display text-2xl sm:text-3xl font-extrabold">Evolución de marketplace-api → JIHOVA</h1>
                <p class="text-sm text-slate-300">Laravel 13 · MySQL 8 Spatial / SQLite Tests · Eloquent HasUuids · Branding Oficial</p>
            </div>
            <div class="text-right">
                <p class="font-display text-4xl font-extrabold text-[#00F0FF]">7 / 7</p>
                <p class="text-xs text-slate-300 uppercase tracking-wider">Fases implementadas</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-[#121728] rounded-2xl border border-white/10 p-5">
                <p class="text-xs font-semibold text-slate-400 uppercase">Rubros activos</p>
                <p class="font-display text-3xl font-extrabold text-white mt-1">{{ $counts['categories'] }}</p>
            </div>
            <div class="bg-[#121728] rounded-2xl border border-white/10 p-5">
                <p class="text-xs font-semibold text-slate-400 uppercase">Tiendas online</p>
                <p class="font-display text-3xl font-extrabold text-[#00F0FF] mt-1">{{ $counts['stores'] }}</p>
            </div>
            <div class="bg-[#121728] rounded-2xl border border-white/10 p-5">
                <p class="text-xs font-semibold text-slate-400 uppercase">Artículos en catálogo</p>
                <p class="font-display text-3xl font-extrabold text-[#FF3366] mt-1">{{ $counts['products'] }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ([
                ['Fase 1: Arquitectura Multi-Tenant', 'UUIDs, SRID 4326 y separación Order → SubOrders por sucursal.'],
                ['Fase 2: Migraciones Laravel 13', 'Compatibilidad corregida con geography(point, 4326) e índices condicionales MySQL/SQLite.'],
                ['Fase 3: Modelos, Enums y Factories', 'Los 12 modelos Eloquent completos con casts, scopes y prevención de lazy-loading.'],
                ['Fase 4: Seeders del Centro Comercial', '5 rubros, 7 tiendas, múltiples sucursales, 20 productos, repartidores y orden demo.'],
                ['Fase 5: Buscador y Geo-Filtro', 'Búsqueda global desde desayunos hasta calzado, cálculo Haversine de distancia, tarifa y ETA.'],
                ['Fase 6: Carrito y Checkout Atómico', 'Agrupación automática por tienda, bloqueo lockForUpdate() y pasarela multi-método.'],
                ['Fase 7: Web + Delivery Propio + API v1', 'Escaparates de tiendas, tracking en tiempo real, panel comercio, panel delivery y API JSON.'],
            ] as [$title, $desc])
                <div class="bg-[#121728] rounded-2xl border border-emerald-400/25 p-5 flex items-start gap-3">
                    <span class="w-7 h-7 rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-300 flex items-center justify-center font-bold text-sm shrink-0">✓</span>
                    <div>
                        <h2 class="font-display font-bold text-white text-sm">{{ $title }}</h2>
                        <p class="text-xs text-slate-400 mt-1">{{ $desc }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.mall>
