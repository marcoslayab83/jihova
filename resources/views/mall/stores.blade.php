<x-layouts.mall title="Directorio de Tiendas · JIHOVA">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-[#00F0FF]">Directorio del Centro Comercial</p>
                <h1 class="font-display text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Todas nuestras tiendas online</h1>
                <p class="text-sm text-slate-400">Cada comercio gestiona su inventario y despacho junto con nuestro equipo de delivery.</p>
            </div>
        </div>

        <!-- Tabs de Rubros -->
        <div class="flex items-center gap-2 overflow-x-auto whitespace-nowrap no-scrollbar pb-1 sm:pb-0 sm:flex-wrap">
            <a href="{{ route('mall.stores') }}" class="shrink-0 px-4 py-2 rounded-full text-xs font-bold border transition {{ $selectedCategory === '' ? 'bg-[#FF3366] text-white border-[#FF3366] shadow-md shadow-[#FF3366]/25' : 'bg-[#121728] text-slate-300 border-white/15 hover:border-[#00F0FF]/50 hover:text-white' }}">
                Todas ({{ $stores->count() }})
            </a>
            @foreach ($categories as $cat)
                <a href="{{ route('mall.stores', ['category' => $cat->slug]) }}" class="shrink-0 px-4 py-2 rounded-full text-xs font-bold border transition {{ $selectedCategory === $cat->slug ? 'bg-[#FF3366] text-white border-[#FF3366] shadow-md shadow-[#FF3366]/25' : 'bg-[#121728] text-slate-300 border-white/15 hover:border-[#00F0FF]/50 hover:text-white' }}">
                    {{ $cat->name }}
                </a>
            @endforeach
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-5">
            @foreach ($stores as $storeItem)
                <x-store-card :item="$storeItem" />
            @endforeach
        </div>
    </div>
</x-layouts.mall>
