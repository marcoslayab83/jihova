<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recibo Térmico · {{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Courier New', Courier, monospace, system-ui;
        }

        @page {
            margin: 0;
        }

        body {
            background-color: #f1f5f9;
            color: #000;
            padding: 16px;
            display: flex;
            justify-content: center;
        }

        .receipt-container {
            background: #fff;
            width: {{ $format === '58mm' ? '58mm' : '80mm' }};
            max-width: 100%;
            padding: 12px 10px;
            font-size: {{ $format === '58mm' ? '11px' : '12px' }};
            line-height: 1.35;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .text-uppercase { text-transform: uppercase; }
        
        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .double-divider {
            border-top: 2px solid #000;
            margin: 8px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 3px 0;
            vertical-align: top;
        }

        .actions-bar {
            position: fixed;
            top: 16px;
            right: 16px;
            display: flex;
            gap: 8px;
            z-index: 1000;
        }

        .btn {
            background: #1e1b4b;
            color: #fff;
            padding: 8px 14px;
            border-radius: 8px;
            font-family: sans-serif;
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }

        .btn-coral {
            background: #f43f5e;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .receipt-container {
                box-shadow: none;
                width: 100%;
                padding: 4px;
            }
            .actions-bar {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="actions-bar">
        <a href="{{ route('merchant.pos.index') }}" class="btn">← Volver al POS</a>
        <button onclick="window.print()" class="btn btn-coral">🖨 Imprimir Recibo</button>
    </div>

    <div class="receipt-container">
        <!-- Encabezado de la Tienda -->
        <div class="text-center">
            <h2 class="font-bold text-uppercase" style="font-size: 15px;">{{ $store->business_name ?? 'JIHOVA STORE' }}</h2>
            <p class="font-bold">RIF: {{ $store->rif ?? 'J-00000000-0' }}</p>
            @if ($store && $store->is_special_taxpayer)
                <p class="font-bold text-uppercase" style="font-size: 10px;">*** CONTRIBUYENTE ESPECIAL ***</p>
            @endif
            <p>{{ $branch->branch_name ?? 'Sucursal Principal' }}</p>
            <p>{{ $branch->address ?? 'Venezuela' }}</p>
            <p>Telf: {{ $store->phone ?? 'N/A' }}</p>
        </div>

        <div class="divider"></div>

        <!-- Metadatos de la Venta -->
        <div>
            <div style="display: flex; justify-content: space-between;">
                <span>Orden: <strong class="font-bold">{{ $order->order_number }}</strong></span>
                <span>{{ $order->created_at->format('d/m/Y') }}</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span>Hora: {{ $order->created_at->format('h:i:s A') }}</span>
                <span>Caja: {{ $cashShift?->register?->name ?? '01' }}</span>
            </div>
            @if ($cashier)
                <p>Cajero: {{ $cashier->full_name }}</p>
            @endif
            <div class="divider"></div>
            <p>Cliente: <span class="font-bold">{{ $order->customer_name ?? 'Consumidor Final' }}</span></p>
            <p>Cédula/RIF: <span class="font-bold">{{ $order->customer_doc ?? 'V-00000000' }}</span></p>
            @if ($order->customer_phone)
                <p>Teléfono: {{ $order->customer_phone }}</p>
            @endif
            <p>Tasa BCV: <span class="font-bold">{{ number_format($rate, 2, ',', '.') }} Bs/$</span></p>
        </div>

        <div class="double-divider"></div>

        <!-- Detalle de Productos -->
        <table>
            <thead>
                <tr style="border-bottom: 1px solid #000;">
                    <th class="text-left">CANT. ARTÍCULO</th>
                    <th class="text-right">P.U ($)</th>
                    <th class="text-right">TOTAL ($)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td colspan="3" class="text-left font-bold" style="padding-top: 4px;">
                            {{ $item->product_name }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-left" style="padding-bottom: 4px; padding-left: 8px;">
                            {{ $item->quantity }} x ${{ number_format((float) $item->unit_price, 2) }}
                        </td>
                        <td class="text-right" style="padding-bottom: 4px;">
                            Bs. {{ number_format($exchangeService->usdToVes((float) $item->unit_price, $rate), 2, ',', '.') }}
                        </td>
                        <td class="text-right font-bold" style="padding-bottom: 4px;">
                            ${{ number_format((float) $item->total_price, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        <!-- Totales Bimonetarios e IGTF -->
        @php
            $subtotalUsd = (float) $order->total_items_amount;
            $subtotalVes = $exchangeService->usdToVes($subtotalUsd, $rate);
            $igtfUsd = (float) ($order->igtf_amount ?? 0);
            $igtfVes = $exchangeService->usdToVes($igtfUsd, $rate);
            $grandTotalUsd = (float) $order->grand_total;
            $grandTotalVes = $exchangeService->usdToVes($grandTotalUsd, $rate);
        @endphp

        <table>
            <tr>
                <td class="text-left">SUBTOTAL:</td>
                <td class="text-right font-bold">${{ number_format($subtotalUsd, 2) }}</td>
            </tr>
            <tr>
                <td class="text-left">SUBTOTAL (VES):</td>
                <td class="text-right">Bs. {{ number_format($subtotalVes, 2, ',', '.') }}</td>
            </tr>
            @if ($igtfUsd > 0)
                <tr>
                    <td class="text-left">IGTF (3% Divisas):</td>
                    <td class="text-right font-bold">+${{ number_format($igtfUsd, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-left">IGTF (VES):</td>
                    <td class="text-right">+Bs. {{ number_format($igtfVes, 2, ',', '.') }}</td>
                </tr>
            @endif
            <tr style="font-size: 14px; font-weight: bold; border-top: 1px dashed #000;">
                <td class="text-left" style="padding-top: 5px;">TOTAL USD:</td>
                <td class="text-right" style="padding-top: 5px;">${{ number_format($grandTotalUsd, 2) }}</td>
            </tr>
            <tr style="font-size: 14px; font-weight: bold;">
                <td class="text-left">TOTAL BS:</td>
                <td class="text-right">Bs. {{ number_format($grandTotalVes, 2, ',', '.') }}</td>
            </tr>
        </table>

        <div class="double-divider"></div>

        <!-- Desglose de Métodos de Pago -->
        <p class="font-bold text-uppercase" style="font-size: 11px;">MÉTODOS DE PAGO:</p>
        <table style="margin-top: 4px;">
            @forelse ($payments as $payment)
                <tr>
                    <td class="text-left">
                        {{ $payment->methodLabel() }}
                        @if ($payment->reference_number)
                            <br><small style="font-size: 9px;">Ref: {{ $payment->reference_number }}</small>
                        @endif
                    </td>
                    <td class="text-right font-bold">
                        @if ($payment->currency === 'USD')
                            ${{ number_format((float) $payment->amount, 2) }}
                        @else
                            Bs. {{ number_format((float) $payment->amount, 2, ',', '.') }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="text-left">Pago Directo:</td>
                    <td class="text-right font-bold">${{ number_format($grandTotalUsd, 2) }}</td>
                </tr>
            @endforelse
        </table>

        <div class="divider"></div>

        <!-- Pie de Recibo -->
        <div class="text-center" style="font-size: 10px; margin-top: 8px;">
            <p class="font-bold">¡GRACIAS POR SU COMPRA!</p>
            <p>Plataforma JIHOVA · www.jihova.app</p>
            <p style="margin-top: 4px;">Canal: {{ $order->channel === 'pos_counter' ? 'Venta en Mostrador' : 'Pedido Online Web' }}</p>
            <p style="margin-top: 6px; font-family: monospace;">*** NO FISCAL / CONTROL INTERNO ***</p>
        </div>
    </div>

    <script>
        // Auto-imprimir al cargar si se especifica ?autoprint=1
        if (new URLSearchParams(window.location.search).get('autoprint') === '1') {
            window.addEventListener('load', () => {
                window.print();
            });
        }
    </script>
</body>
</html>
