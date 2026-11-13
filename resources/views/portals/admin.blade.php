<x-layouts.mall title="Administración del Centro Comercial · JIHOVA">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-[#00F0FF]">Torre de Control</p>
                <h1 class="font-display text-2xl sm:text-3xl font-extrabold text-white">Administración del Centro Comercial</h1>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('merchant.dashboard') }}" class="px-4 py-2 rounded-xl border border-white/15 bg-[#121728] text-xs font-semibold text-slate-200 hover:border-[#00F0FF] hover:text-white transition">Vista Comercios</a>
                <a href="{{ route('driver.dashboard') }}" class="px-4 py-2 rounded-xl border border-white/15 bg-[#121728] text-xs font-semibold text-slate-200 hover:border-[#00F0FF] hover:text-white transition">Vista Delivery</a>
                <a href="{{ route('mall.progress') }}" class="px-4 py-2 rounded-xl bg-[#FF3366] hover:bg-[#ff4d7a] text-white text-xs font-bold shadow-md shadow-[#FF3366]/25 transition">Ver Progreso</a>
            </div>
        </div>

        <!-- Métricas Globales -->
        <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
            <div class="bg-[#121728] rounded-2xl border border-white/10 p-4">
                <p class="text-xs text-slate-400">GMV Pagado</p>
                <p class="font-display text-xl font-extrabold text-white mt-1">${{ number_format($metrics['gmv'], 2) }}</p>
            </div>
            <div class="bg-[#121728] rounded-2xl border border-white/10 p-4">
                <p class="text-xs text-slate-400">Comisiones</p>
                <p class="font-display text-xl font-extrabold text-emerald-300 mt-1">${{ number_format($metrics['commissions'], 2) }}</p>
            </div>
            <div class="bg-[#121728] rounded-2xl border border-white/10 p-4">
                <p class="text-xs text-slate-400">Tiendas activas</p>
                <p class="font-display text-xl font-extrabold text-[#00F0FF] mt-1">{{ $metrics['active_stores'] }}</p>
            </div>
            <div class="bg-[#121728] rounded-2xl border border-white/10 p-4">
                <p class="text-xs text-slate-400">Por aprobar</p>
                <p class="font-display text-xl font-extrabold text-amber-300 mt-1">{{ $metrics['pending_stores'] }}</p>
            </div>
            <div class="bg-[#121728] rounded-2xl border border-white/10 p-4">
                <p class="text-xs text-slate-400">Envíos activos</p>
                <p class="font-display text-xl font-extrabold text-[#FF3366] mt-1">{{ $metrics['active_sub_orders'] }}</p>
            </div>
            <div class="bg-[#121728] rounded-2xl border border-white/10 p-4">
                <p class="text-xs text-slate-400">Drivers en línea</p>
                <p class="font-display text-xl font-extrabold text-violet-300 mt-1">{{ $metrics['online_drivers'] }}</p>
            </div>
        </div>

        <!-- Gestión de Tiendas -->
        <section class="bg-[#121728] rounded-3xl border border-white/10 p-6 space-y-4 shadow-xl">
            <h2 class="font-display text-lg font-extrabold text-white">Tiendas Registradas ({{ $stores->count() }})</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="text-slate-400 border-b border-white/10">
                        <tr>
                            <th class="py-2 pr-3">Tienda</th>
                            <th class="py-2 px-3">Rubro</th>
                            <th class="py-2 px-3">Propietario</th>
                            <th class="py-2 px-3">Comisión</th>
                            <th class="py-2 px-3">Estado</th>
                            <th class="py-2 pl-3 text-right">Cambiar Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @foreach ($stores as $store)
                            <tr>
                                <td class="py-3 pr-3 font-bold text-white">{{ $store->business_name }}</td>
                                <td class="py-3 px-3 text-slate-300">{{ $store->category?->name ?? '-' }}</td>
                                <td class="py-3 px-3 text-slate-300">{{ $store->owner->full_name }}</td>
                                <td class="py-3 px-3 font-mono text-[#00F0FF]">{{ $store->commission_rate }}%</td>
                                <td class="py-3 px-3"><x-status-badge :status="$store->status" /></td>
                                <td class="py-3 pl-3 text-right">
                                    <form action="{{ route('admin.stores.status', $store) }}" method="POST" class="inline-flex items-center gap-1.5">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" class="px-2 py-1 rounded-lg border border-white/15 bg-[#0B0E17] text-white focus:outline-none focus:border-[#00F0FF]">
                                            @foreach (\App\Enums\StoreStatus::cases() as $st)
                                                <option value="{{ $st->value }}" @selected($store->status === $st)>{{ $st->label() }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-[#6C28FE] hover:bg-[#7d42ff] text-white font-semibold cursor-pointer">OK</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Monitor de Sub-Órdenes y Despacho Manual -->
        <section class="bg-[#121728] rounded-3xl border border-white/10 p-6 space-y-4 shadow-xl">
            <h2 class="font-display text-lg font-extrabold text-white">Despacho Global de Sub-Pedidos</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="text-slate-400 border-b border-white/10">
                        <tr>
                            <th class="py-2 pr-3">Código</th>
                            <th class="py-2 px-3">Tienda</th>
                            <th class="py-2 px-3">Cliente</th>
                            <th class="py-2 px-3">Total</th>
                            <th class="py-2 px-3">Estado</th>
                            <th class="py-2 pl-3">Repartidor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @foreach ($subOrders as $subOrder)
                            <tr>
                                <td class="py-3 pr-3 font-mono font-bold text-[#00F0FF]">{{ $subOrder->sub_order_code }}</td>
                                <td class="py-3 px-3 text-white">{{ $subOrder->branch->store->business_name }}</td>
                                <td class="py-3 px-3 text-slate-300">{{ $subOrder->order->customer->full_name }}</td>
                                <td class="py-3 px-3 font-bold text-white">${{ number_format((float) $subOrder->subtotal + (float) $subOrder->delivery_fee, 2) }}</td>
                                <td class="py-3 px-3"><x-status-badge :status="$subOrder->status" /></td>
                                <td class="py-3 pl-3">
                                    @if ($subOrder->driver)
                                        <span class="font-semibold text-emerald-300">🛵 {{ $subOrder->driver->full_name }}</span>
                                    @elseif ($subOrder->status === \App\Enums\SubOrderStatus::ReadyForPickup && $drivers->isNotEmpty())
                                        <form action="{{ route('admin.sub-orders.assign-driver', $subOrder) }}" method="POST" class="inline-flex gap-1.5">
                                            @csrf
                                            <select name="driver_id" class="px-2 py-1 rounded-lg border border-white/15 bg-[#0B0E17] text-white focus:outline-none focus:border-[#00F0FF]">
                                                @foreach ($drivers as $drv)
                                                    <option value="{{ $drv->user_id }}">{{ $drv->user->full_name }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-[#FF3366] hover:bg-[#ff4d7a] text-white font-semibold cursor-pointer">Asignar</button>
                                        </form>
                                    @else
                                        <span class="text-slate-500">En preparación</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-layouts.mall>
