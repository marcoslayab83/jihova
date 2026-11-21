<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Comanda Térmica · {{ $subOrder->sub_order_code }}</title>
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
            font-size: {{ $format === '58mm' ? '12px' : '13px' }};
            line-height: 1.4;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .text-uppercase { text-transform: uppercase; }
        
        .divider {
            border-top: 2px dashed #000;
            margin: 10px 0;
        }

        .double-divider {
            border-top: 3px solid #000;
            margin: 10px 0;
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
        <button onclick="window.print()" class="btn btn-coral">🖨 Imprimir Comanda</button>
    </div>

    <div class="receipt-container">
        <div class="text-center">
            <h1 class="font-bold text-uppercase" style="font-size: 18px;">COMANDA DE PREPARACIÓN</h1>
            <p class="font-bold" style="font-size: 14px; margin-top: 4px;">{{ $store->business_name }}</p>
            <p>{{ $branch->branch_name }}</p>
        </div>

        <div class="double-divider"></div>

        <div>
            <p style="font-size: 15px;"><span class="font-bold">PEDIDO WEB:</span> <span class="font-bold">{{ $subOrder->sub_order_code }}</span></p>
            <p>Orden Madre: {{ $order->order_number }}</p>
            <p>Hora de Entrada: {{ $subOrder->created_at->format('h:i:s A') }} ({{ $subOrder->created_at->format('d/m/Y') }})</p>
            <div class="divider"></div>
            <p><span class="font-bold">Cliente:</span> {{ $order->customer?->full_name ?? $order->customer_name ?? 'Cliente Web' }}</p>
            <p><span class="font-bold">Teléfono:</span> {{ $order->customer?->phone ?? $order->customer_phone ?? 'N/A' }}</p>
            <p><span class="font-bold">Entrega:</span> {{ $order->address?->street_address ?? 'Recoger en mostrador' }}</p>
            @if ($order->customer_notes)
                <div style="margin-top: 6px; padding: 6px; border: 1px solid #000; font-weight: bold;">
                    ⚠️ NOTA CLIENTE: {{ $order->customer_notes }}
                </div>
            @endif
        </div>

        <div class="double-divider"></div>

        <div class="text-center font-bold" style="font-size: 14px; margin-bottom: 6px;">
            LISTA DE ARTÍCULOS A PREPARAR
        </div>

        <table style="width: 100%; border-collapse: collapse;">
            <tbody>
                @foreach ($items as $item)
                    <tr style="border-bottom: 1px dashed #000;">
                        <td style="padding: 8px 0; font-size: 16px; font-weight: bold; width: 45px; vertical-align: top;">
                            [{{ $item->quantity }}x]
                        </td>
                        <td style="padding: 8px 0; font-size: 14px; font-weight: bold; vertical-align: top;">
                            {{ $item->product_name }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="double-divider"></div>

        <div class="text-center" style="font-size: 11px;">
            <p class="font-bold">TOTAL ÍTEMS: {{ $items->sum('quantity') }}</p>
            <p style="margin-top: 4px;">*** PLATAFORMA JIHOVA.APP ***</p>
        </div>
    </div>

    <script>
        if (new URLSearchParams(window.location.search).get('autoprint') === '1') {
            window.addEventListener('load', () => {
                window.print();
            });
        }
    </script>
</body>
</html>
