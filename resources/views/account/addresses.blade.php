<x-layouts.mall title="Mis Direcciones · JIHOVA">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-[#00F0FF]">Libreta de Entrega</p>
            <h1 class="font-display text-2xl sm:text-3xl font-extrabold text-white">Mis Direcciones de Entrega</h1>
            <p class="text-sm text-slate-400">Guarda tus ubicaciones con coordenadas GPS para calcular tarifas y cobertura en segundos.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <div class="lg:col-span-7 space-y-4">
                @forelse ($addresses as $address)
                    <div class="bg-[#121728] rounded-3xl border {{ $address->is_default ? 'border-[#00F0FF]/60 shadow-[0_0_24px_-8px_rgba(0,240,255,0.35)]' : 'border-white/10' }} p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h2 class="font-display font-bold text-white">{{ $address->title }}</h2>
                                @if ($address->is_default)
                                    <span class="px-2.5 py-0.5 rounded-full bg-[#00F0FF]/15 border border-[#00F0FF]/40 text-[#00F0FF] text-[11px] font-bold">Predeterminada</span>
                                @endif
                            </div>
                            <p class="text-sm text-slate-300">{{ $address->street_address }}</p>
                            @if ($address->reference)
                                <p class="text-xs text-slate-400">Ref: {{ $address->reference }}</p>
                            @endif
                            <p class="text-[11px] font-mono text-slate-500">GPS: {{ number_format($address->latitude, 5) }}, {{ number_format($address->longitude, 5) }}</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @unless ($address->is_default)
                                <form action="{{ route('addresses.default', $address) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-xs font-semibold text-white cursor-pointer">
                                        Usar por defecto
                                    </button>
                                </form>
                            @endunless
                            <form action="{{ route('addresses.destroy', $address) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 rounded-xl border border-[#FF3366]/40 text-[#FF6B8B] hover:bg-[#FF3366]/15 text-xs font-semibold cursor-pointer">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="bg-[#121728] rounded-3xl border border-white/10 p-8 text-center text-sm text-slate-400">
                        Aún no tienes direcciones guardadas. Agrega una usando el formulario.
                    </div>
                @endforelse
            </div>

            <div class="lg:col-span-5 bg-[#121728] rounded-3xl border border-white/10 p-6 space-y-4 shadow-xl">
                <h2 class="font-display text-lg font-extrabold text-white">Agregar nueva dirección</h2>
                <form action="{{ route('addresses.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Alias</label>
                        <input type="text" name="title" value="{{ old('title') }}" placeholder="Ej. Casa, Trabajo" required class="w-full px-3.5 py-2 rounded-xl border border-white/15 bg-[#0B0E17] text-white placeholder:text-slate-500 text-sm focus:outline-none focus:border-[#00F0FF]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Calle, edificio o casa</label>
                        <input type="text" name="street_address" value="{{ old('street_address') }}" required class="w-full px-3.5 py-2 rounded-xl border border-white/15 bg-[#0B0E17] text-white text-sm focus:outline-none focus:border-[#00F0FF]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Referencia</label>
                        <input type="text" name="reference" value="{{ old('reference') }}" placeholder="Opcional" class="w-full px-3.5 py-2 rounded-xl border border-white/15 bg-[#0B0E17] text-white placeholder:text-slate-500 text-sm focus:outline-none focus:border-[#00F0FF]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Autocompletar zona GPS</label>
                        <select id="addr-zone-preset" class="w-full px-3.5 py-2 rounded-xl border border-white/15 bg-[#0B0E17] text-white text-sm focus:outline-none focus:border-[#00F0FF]">
                            @foreach ($presetZones as $zone)
                                <option value="{{ $zone['latitude'] }}|{{ $zone['longitude'] }}">{{ $zone['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Latitud</label>
                            <input type="number" step="0.000001" id="addr-lat" name="latitude" value="{{ old('latitude', $presetZones[0]['latitude']) }}" required class="w-full px-3 py-2 rounded-xl border border-white/15 bg-[#0B0E17] text-white text-sm focus:outline-none focus:border-[#00F0FF]">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Longitud</label>
                            <input type="number" step="0.000001" id="addr-lng" name="longitude" value="{{ old('longitude', $presetZones[0]['longitude']) }}" required class="w-full px-3 py-2 rounded-xl border border-white/15 bg-[#0B0E17] text-white text-sm focus:outline-none focus:border-[#00F0FF]">
                        </div>
                    </div>
                    <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                        <input type="checkbox" name="is_default" value="1" class="rounded border-white/25 bg-[#0B0E17] accent-[#FF3366]">
                        <span>Establecer como dirección predeterminada</span>
                    </label>
                    <button type="submit" class="w-full py-3 rounded-2xl bg-[#FF3366] hover:bg-[#ff4d7a] text-white text-sm font-bold shadow-lg shadow-[#FF3366]/30 transition cursor-pointer">
                        Guardar dirección
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('addr-zone-preset')?.addEventListener('change', (e) => {
            const [lat, lng] = e.target.value.split('|');
            document.getElementById('addr-lat').value = lat;
            document.getElementById('addr-lng').value = lng;
        });
    </script>
    @endpush
</x-layouts.mall>
