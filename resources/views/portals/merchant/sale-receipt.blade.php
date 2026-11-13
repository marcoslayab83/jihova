<x-layouts.mall title="Recibo de Venta {{ $sale->sale_number }} · JIHOVA">
    <div class="max-w-2xl mx-auto px-4 py-8 space-y-6">
        <div class="flex items-center justify-between">
            <a href="{{ route('merchant.dashboard') }}" class="text-xs font-bold text-[#00F0FF] hover:underline flex items-center gap-1">
                ← Volver al Panel del Comerciante
            </a>
            <button onclick="window.print()" class="px-3.5 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition cursor-pointer">
                🖨 Imprimir Recibo
            </button>
        </div>

        <div class="rounded-3xl border border-white/15 bg-[#121728] p-6 sm:p-8 shadow-2xl space-y-6 text-white">
            <!-- Encabezado del Recibo -->
            <div class="text-center pb-6 border-b border-white/10 space-y-1">
                <span class="inline-block font-display text-xl font-extrabold text-[#00F0FF]">JIHOVA</span>
                <p class="text-xs text-slate-400">El Primer Centro Comercial Online de Venezuela</p>
                <h1 class="font-display text-2xl font-extrabold mt-3">{{ $sale->store->business_name }}</h1>
                <p class="text-xs text-slate-300">{{ $sale->branch->branch_name }} · {{ $sale->branch->address }}</p>
                <div class="mt-3 inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/5 border border-white/10 text-xs font-mono">
                    <span>Recibo:</span>
                    <strong class="text-[#00F0FF]">{{ $sale->sale_number }}</strong>
                </div>
            </div>

            <!-- Datos de la Venta -->
            <div class="grid grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="block text-slate-400">Fecha y Hora:</span>
                    <strong class="text-white">{{ $sale->sold_at->format('d/m/Y H:i:s') }}</strong>
                </div>
                <div>
                    <span class="block text-slate-400">Canal de Venta:</span>
                    <strong class="text-white">{{ $sale->channelLabel() }}</strong>
                </div>
                <div>
                    <span class="block text-slate-400">Método de Pago:</span>
                    <strong class="text-white">{{ $sale->paymentMethodLabel() }}</strong>
                </div>
                <div>
                    <span class="block text-slate-400">Cliente:</span>
                    <strong class="text-white">{{ $sale->customer_name ?? 'Consumidor Final' }}</strong>
                </div>
                @if ($sale->payment_reference)
                    <div class="col-span-2">
                        <span class="block text-slate-400">Referencia de Pago:</span>
                        <strong class="text-[#00F0FF] font-mono">{{ $sale->payment_reference }}</strong>
                    </div>
                @endif
            </div>

            <!-- Tabla de Artículos -->
            <div class="border-t border-b border-white/10 py-4 space-y-3">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="text-slate-400 border-b border-white/10">
                            <th class="text-left pb-2">Cant.</th>
                            <th class="text-left pb-2">Producto</th>
                            <th class="text-right pb-2">P. Unit</th>
                            <th class="text-right pb-2">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach ($sale->items as $item)
                            <tr>
                                <td class="py-2 font-bold">{{ $item->quantity }}x</td>
                                <td class="py-2 text-slate-200">{{ $item->product_name }}</td>
                                <td class="py-2 text-right text-slate-400">${{ number_format((float) $item->unit_price, 2) }}</td>
                                <td class="py-2 text-right font-bold text-white">${{ number_format((float) $item->total_price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Totales -->
            <div class="space-y-1.5 text-right text-xs">
                <div class="flex justify-between text-slate-400">
                    <span>Subtotal:</span>
                    <span>${{ number_format((float) $sale->subtotal, 2) }}</span>
                </div>
                @if ($sale->discount_amount > 0)
                    <div class="flex justify-between text-rose-400">
                        <span>Descuento aplicado:</span>
                        <span>-${{ number_format((float) $sale->discount_amount, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-lg font-extrabold text-[#00F0FF] pt-2 border-t border-white/10">
                    <span>TOTAL PAGADO:</span>
                    <span>${{ number_format((float) $sale->total_amount, 2) }} USD</span>
                </div>
            </div>

            <p class="text-center text-[11px] text-slate-500 pt-4 border-t border-white/10">
                ¡Gracias por su compra! Comprobante emitido a través de la Suite de Comerciantes JIHOVA.
            </p>
        </div>
    </div>
</x-layouts.mall>
