<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS Omnicanal · {{ $activeStore->business_name }} · JIHOVA</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|syne:700,800" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
            user-select: none;
            -webkit-user-select: none;
        }
        .font-display {
            font-family: 'Syne', ui-sans-serif, system-ui, sans-serif;
        }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        /* Paleta Oficial JIHOVA */
        :root {
            --jihova-indigo: #1E1B4B;
            --jihova-dark: #0B0E17;
            --jihova-surface: #121728;
            --jihova-coral: #F43F5E;
            --jihova-cyan: #06B6D4;
            --jihova-alabaster: #F8FAFC;
        }

        .pulse-cyan {
            box-shadow: 0 0 15px rgba(6, 182, 212, 0.45);
        }
        .pulse-coral {
            box-shadow: 0 0 20px rgba(244, 63, 94, 0.45);
        }
    </style>
</head>
<body class="h-full bg-[#0B0E17] text-white flex flex-col overflow-hidden">

    <!-- BARRA SUPERIOR: Índigo Soberano (#1E1B4B) con Información del Comercio, Tasa BCV y Caja -->
    <header class="h-16 bg-[#1E1B4B] border-b border-white/10 px-4 flex items-center justify-between shrink-0 z-30">
        <!-- Logo y Selección de Sucursal -->
        <div class="flex items-center gap-4">
            <a href="{{ route('merchant.dashboard') }}" class="flex items-center gap-2 group text-white hover:text-[#06B6D4] transition" title="Volver al Panel">
                <span class="font-display font-black text-xl tracking-wider text-white">JIHOVA</span>
                <span class="px-2 py-0.5 rounded-full bg-[#06B6D4]/20 text-[#06B6D4] text-[10px] font-bold tracking-widest uppercase">POS</span>
            </a>

            <div class="h-6 w-px bg-white/15"></div>

            <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-white truncate max-w-[180px] sm:max-w-xs">{{ $activeStore->business_name }}</span>
                <span class="text-xs text-slate-400">({{ $activeBranch->branch_name }})</span>
                @if ($isSpecialTaxpayer)
                    <span class="hidden sm:inline-block px-2 py-0.5 rounded-md bg-amber-500/20 text-amber-300 text-[10px] font-bold border border-amber-400/30">
                        Contribuyente Especial (IGTF 3%)
                    </span>
                @endif
            </div>
        </div>

        <!-- Indicadores Centrales: Tasa BCV y Estado de Conexión -->
        <div class="hidden md:flex items-center gap-3">
            <!-- Indicador Tasa BCV -->
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-black/30 border border-white/10 text-xs font-medium">
                <span class="text-[#06B6D4] font-bold">Tasa BCV:</span>
                <span class="font-mono font-bold text-white" id="headerBcvRate">Bs. {{ number_format($bcvRate, 2, ',', '.') }}</span>
                <span class="text-[10px] text-slate-400">/ USD</span>
            </div>

            <!-- Indicador de Red (Online / Offline PWA) -->
            <div id="networkStatusBadge" class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-xs font-semibold">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span id="networkStatusText">Online</span>
            </div>
        </div>

        <!-- Botones de Acción: Pedidos Web en Vivo, Caja y Cajero -->
        <div class="flex items-center gap-2 sm:gap-3">
            <!-- Bandeja de Pedidos Web en Vivo (Notificaciones con pulso) -->
            <button id="btnToggleOnlineOrders" type="button" class="relative flex items-center gap-2 px-3 py-1.5 rounded-xl bg-[#121728] border border-white/15 hover:border-[#06B6D4] text-xs font-bold text-white transition cursor-pointer">
                <span class="text-sm">🔔</span>
                <span class="hidden sm:inline">Pedidos Web</span>
                <span id="liveOrdersBadge" class="hidden px-1.5 py-0.5 rounded-full bg-[#F43F5E] text-white text-[11px] font-black pulse-coral">
                    0
                </span>
            </button>

            <!-- Estado de Caja / Corte -->
            @if ($activeShift)
                <button id="btnOpenShiftModal" type="button" class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-500/20 border border-emerald-400/40 hover:bg-emerald-500/30 text-xs font-bold text-emerald-300 transition cursor-pointer">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span>Caja Abierta</span>
                    <span class="text-[10px] text-emerald-400/80">#{{ substr($activeShift->id, 0, 6) }}</span>
                </button>
            @else
                <button id="btnTriggerOpenShift" type="button" class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-[#F43F5E] hover:bg-[#F43F5E]/90 text-xs font-bold text-white transition cursor-pointer pulse-coral">
                    <span>⚠️</span>
                    <span>Abrir Turno de Caja</span>
                </button>
            @endif

            <!-- Atajos de Teclado Info -->
            <div class="hidden lg:flex items-center gap-1 text-[11px] text-slate-400 bg-black/20 px-2 py-1 rounded-lg">
                <span class="px-1.5 py-0.5 rounded bg-white/10 text-white font-mono font-bold">F2</span> Cobrar
                <span class="px-1.5 py-0.5 rounded bg-white/10 text-white font-mono font-bold ml-1">F4</span> Cliente
                <span class="px-1.5 py-0.5 rounded bg-white/10 text-white font-mono font-bold ml-1">ESC</span> Cancelar
            </div>

            <!-- Cajero -->
            <div class="flex items-center gap-2 pl-2 border-l border-white/10">
                <div class="w-8 h-8 rounded-full bg-[#06B6D4]/20 border border-[#06B6D4]/40 flex items-center justify-center font-bold text-[#06B6D4] text-xs">
                    {{ strtoupper(substr($user->full_name, 0, 2)) }}
                </div>
            </div>
        </div>
    </header>

    <!-- CUERPO PRINCIPAL: 2 Columnas (Izquierda: Catálogo Alabastro | Derecha: Ticket Índigo) -->
    <main class="flex-1 flex overflow-hidden">
        
        <!-- COLUMNA IZQUIERDA: Catálogo de Productos (Fondo Blanco Alabastro #F8FAFC, Tarjetas Blancas #FFFFFF) -->
        <section class="flex-1 flex flex-col bg-[#F8FAFC] text-slate-900 overflow-hidden relative">
            
            <!-- Barra de Búsqueda y Lector de Código de Barras USB -->
            <div class="p-3 sm:p-4 bg-white border-b border-slate-200 flex flex-col sm:flex-row items-stretch sm:items-center gap-3 shrink-0 shadow-sm">
                <!-- Buscador rápido con autofocus -->
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        🔍
                    </span>
                    <input type="text" id="barcodeSearchInput" autofocus
                        placeholder="Escanear código de barras USB o buscar producto por nombre/SKU... [Enter]"
                        class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-[#06B6D4] focus:border-transparent transition placeholder:text-slate-400">
                    <button id="btnClearSearch" type="button" class="hidden absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                        ✕
                    </button>
                </div>

                <!-- Filtro por Categorías (Tabs rápidos) -->
                <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-1">
                    <button type="button" data-category="all" class="category-pill active px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap bg-[#1E1B4B] text-white">
                        Todos
                    </button>
                    @foreach ($categories as $cat)
                        <button type="button" data-category="{{ $cat->id }}" class="category-pill px-3.5 py-1.5 rounded-xl text-xs font-semibold transition whitespace-nowrap bg-slate-100 text-slate-600 hover:bg-slate-200">
                            {{ $cat->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Cuadrícula de Productos Táctil -->
            <div id="productGrid" class="flex-1 overflow-y-auto p-3 sm:p-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3 content-start">
                <!-- Se poblará vía JavaScript reactivo para filtrado instantáneo -->
            </div>

            <!-- Banner Alerta si no hay turno abierto -->
            @if (! $activeShift)
                <div class="absolute inset-0 bg-[#0B0E17]/80 backdrop-blur-sm z-20 flex items-center justify-center p-4">
                    <div class="max-w-md w-full bg-[#121728] border border-white/15 rounded-3xl p-6 text-center text-white shadow-2xl space-y-4">
                        <div class="w-16 h-16 rounded-2xl bg-[#F43F5E]/20 text-[#F43F5E] text-3xl flex items-center justify-center mx-auto border border-[#F43F5E]/30">
                            🔒
                        </div>
                        <h2 class="font-display font-black text-xl">Turno de Caja Cerrado</h2>
                        <p class="text-xs text-slate-300">
                            Para comenzar a facturar en mostrador y registrar transacciones multimoneda, debe abrir el turno de la caja registradora con los fondos iniciales.
                        </p>
                        <button type="button" onclick="openShiftModalAction()" class="w-full py-3 rounded-xl bg-[#F43F5E] hover:bg-[#F43F5E]/90 text-white font-bold text-sm transition pulse-coral cursor-pointer">
                            Abrir Turno de Caja Ahora
                        </button>
                    </div>
                </div>
            @endif
        </section>

        <!-- COLUMNA DERECHA: Virtual Ticket / Carrito de Venta (Índigo Soberano #121728 / #1E1B4B) -->
        <aside class="w-80 sm:w-96 md:w-[420px] bg-[#121728] border-l border-white/10 flex flex-col shrink-0">
            
            <!-- Encabezado del Ticket y Datos del Cliente -->
            <div class="p-3 sm:p-4 border-b border-white/10 bg-[#1E1B4B]/60 space-y-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-black font-display text-white">TICKET DE VENTA</span>
                        <span id="ticketItemCountBadge" class="px-2 py-0.5 rounded-full bg-[#06B6D4]/20 text-[#06B6D4] text-xs font-bold">0 ítems</span>
                    </div>
                    <button type="button" id="btnClearTicket" class="text-xs text-slate-400 hover:text-[#F43F5E] transition" title="Limpiar Ticket [ESC]">
                        Limpiar [ESC]
                    </button>
                </div>

                <!-- Botón / Pill de Datos del Cliente (F4) -->
                <button type="button" id="btnCustomerModal" class="w-full flex items-center justify-between px-3 py-2 rounded-xl bg-white/5 border border-white/10 hover:border-[#06B6D4] transition text-left cursor-pointer">
                    <div class="truncate">
                        <span class="text-[10px] text-slate-400 block">Cliente [F4]:</span>
                        <span id="ticketCustomerDisplay" class="text-xs font-bold text-white truncate block">Consumidor Final (V-00000000)</span>
                    </div>
                    <span class="text-xs text-[#06B6D4] shrink-0 font-bold">Editar ✎</span>
                </button>
            </div>

            <!-- Lista de Artículos en el Ticket -->
            <div id="ticketItemsList" class="flex-1 overflow-y-auto p-3 space-y-2">
                <!-- Se llena dinámicamente con JS -->
                <div id="emptyTicketState" class="h-full flex flex-col items-center justify-center text-center text-slate-500 p-6 space-y-3">
                    <span class="text-4xl">🛒</span>
                    <p class="text-xs">El ticket está vacío.<br>Escanee un código de barras o haga clic en un producto.</p>
                </div>
            </div>

            <!-- Resumen de Totales Bimonetarios (USD y VES) e IGTF -->
            <div class="p-4 border-t border-white/10 bg-[#0B0E17]/90 space-y-2">
                <!-- Subtotal -->
                <div class="flex justify-between text-xs text-slate-400">
                    <span>Subtotal:</span>
                    <span id="ticketSubtotalUsd" class="font-bold text-white font-mono">$0.00</span>
                </div>
                <div class="flex justify-between text-[11px] text-slate-500">
                    <span>Subtotal en Bolívares:</span>
                    <span id="ticketSubtotalVes" class="font-mono">Bs. 0,00</span>
                </div>

                <!-- IGTF Estimado (3% sobre divisas si aplica) -->
                @if ($isSpecialTaxpayer)
                    <div class="flex justify-between text-xs text-amber-300/90 pt-1 border-t border-white/5">
                        <span class="flex items-center gap-1">
                            <span>IGTF 3% (Divisas):</span>
                            <span class="text-[9px] px-1 py-0.2 rounded bg-amber-500/20 text-amber-300">Ley</span>
                        </span>
                        <span id="ticketIgtfEstimate" class="font-bold font-mono">$0.00</span>
                    </div>
                @endif

                <!-- Total Gran Total -->
                <div class="pt-2 border-t border-white/10">
                    <div class="flex items-baseline justify-between">
                        <span class="text-xs font-bold text-slate-300">TOTAL A COBRAR:</span>
                        <div class="text-right">
                            <div id="ticketGrandTotalUsd" class="font-display font-black text-2xl text-[#06B6D4] font-mono leading-tight">
                                $0.00
                            </div>
                            <div id="ticketGrandTotalVes" class="text-xs text-slate-400 font-mono">
                                ≈ Bs. 0,00
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botón Principal: COBRAR [F2] en Coral Pulso (#F43F5E) -->
                <button type="button" id="btnTriggerCheckout" disabled
                    class="w-full mt-2 py-3.5 px-4 rounded-2xl bg-[#F43F5E] hover:bg-[#F43F5E]/90 disabled:opacity-40 disabled:cursor-not-allowed text-white font-display font-black text-base tracking-wide flex items-center justify-center gap-2 transition cursor-pointer pulse-coral">
                    <span>⚡ COBRAR [F2]</span>
                </button>
            </div>
        </aside>
    </main>

    <!-- ========================================== -->
    <!-- MODAL 1: COBRO MULTIMONEDA Y MIXTO (F2)   -->
    <!-- ========================================== -->
    <div id="paymentModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-[#121728] border border-white/15 rounded-3xl max-w-2xl w-full max-h-[90vh] flex flex-col shadow-2xl overflow-hidden">
            <!-- Header Modal -->
            <div class="p-4 bg-[#1E1B4B] border-b border-white/10 flex items-center justify-between">
                <div>
                    <h3 class="font-display font-black text-lg text-white">Procesar Cobro Multimoneda</h3>
                    <p class="text-xs text-slate-300">Tasa BCV oficial: <strong class="text-[#06B6D4]">Bs. {{ number_format($bcvRate, 2, ',', '.') }}</strong></p>
                </div>
                <button type="button" onclick="closePaymentModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center font-bold text-sm">✕</button>
            </div>

            <!-- Cuerpo Modal -->
            <div class="p-5 overflow-y-auto space-y-4">
                <!-- Totales a Pagar -->
                <div class="grid grid-cols-2 gap-3 p-3 rounded-2xl bg-black/40 border border-white/10">
                    <div>
                        <span class="text-[11px] text-slate-400 block">Total a Pagar (USD):</span>
                        <span id="modalTotalDueUsd" class="font-display font-black text-2xl text-white font-mono">$0.00</span>
                    </div>
                    <div>
                        <span class="text-[11px] text-slate-400 block">Total a Pagar (VES):</span>
                        <span id="modalTotalDueVes" class="font-display font-black text-xl text-[#06B6D4] font-mono">Bs. 0,00</span>
                    </div>
                </div>

                <!-- Formulario para Agregar Pago Mixto -->
                <div class="p-4 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                    <span class="text-xs font-bold text-slate-200 block">Agregar Método de Pago:</span>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <!-- Método -->
                        <div>
                            <label class="text-[10px] text-slate-400 block mb-1">Método</label>
                            <select id="payMethodSelect" class="w-full bg-[#1E1B4B] border border-white/15 rounded-xl px-2.5 py-2 text-xs text-white focus:outline-none focus:border-[#06B6D4]">
                                <option value="efectivo_usd" data-currency="USD">💵 Efectivo USD ($)</option>
                                <option value="efectivo_ves" data-currency="VES">💵 Efectivo Bolívares (Bs)</option>
                                <option value="pago_movil" data-currency="VES">📱 Pago Móvil (Bs)</option>
                                <option value="punto_venta" data-currency="VES">💳 Punto de Venta (Bs)</option>
                                <option value="zelle" data-currency="USD">🏦 Zelle ($)</option>
                                <option value="jihova_wallet" data-currency="USD">🟣 JIHOVA Wallet ($)</option>
                            </select>
                        </div>

                        <!-- Monto -->
                        <div>
                            <label class="text-[10px] text-slate-400 block mb-1">Monto en <span id="payCurrencyLabel" class="text-[#06B6D4] font-bold">USD</span></label>
                            <input type="number" step="0.01" min="0" id="payAmountInput" placeholder="0.00"
                                class="w-full bg-[#1E1B4B] border border-white/15 rounded-xl px-2.5 py-2 text-xs text-white font-mono focus:outline-none focus:border-[#06B6D4]">
                        </div>

                        <!-- Referencia / Botón Agregar -->
                        <div class="flex items-end gap-2">
                            <div class="flex-1">
                                <label class="text-[10px] text-slate-400 block mb-1">Referencia</label>
                                <input type="text" id="payRefInput" placeholder="Opcional"
                                    class="w-full bg-[#1E1B4B] border border-white/15 rounded-xl px-2.5 py-2 text-xs text-white font-mono focus:outline-none focus:border-[#06B6D4]">
                            </div>
                            <button type="button" id="btnAddPaymentRow" class="px-3 py-2 rounded-xl bg-[#06B6D4] hover:bg-[#06B6D4]/90 text-slate-900 font-bold text-xs transition cursor-pointer shrink-0">
                                + Añadir
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Lista de Pagos Ingresados -->
                <div>
                    <span class="text-xs font-bold text-slate-300 block mb-2">Desglose de Pagos Registrados:</span>
                    <div id="paymentsListTable" class="rounded-xl border border-white/10 overflow-hidden divide-y divide-white/5 bg-black/20 text-xs">
                        <div id="noPaymentsPrompt" class="p-4 text-center text-slate-400">
                            Aún no ha ingresado ningún método de pago.
                        </div>
                    </div>
                </div>

                <!-- Saldo Restante o Vuelto (Cambio) -->
                <div class="p-3.5 rounded-2xl bg-black/40 border border-white/10 space-y-2">
                    <div class="flex justify-between text-xs">
                        <span class="text-slate-400">Total Ingresado:</span>
                        <span id="modalTotalPaidDisplay" class="font-bold text-white font-mono">$0.00</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-slate-400">IGTF Calculado (3% Divisas):</span>
                        <span id="modalIgtfDisplay" class="font-bold text-amber-300 font-mono">+$0.00</span>
                    </div>
                    <div id="balanceDueContainer" class="flex justify-between text-sm font-bold pt-2 border-t border-white/10 text-rose-400">
                        <span>Falta por Pagar:</span>
                        <span id="modalRemainingDueDisplay" class="font-mono">$0.00</span>
                    </div>
                    <div id="changeContainer" class="hidden flex justify-between text-sm font-bold pt-2 border-t border-white/10 text-emerald-400">
                        <span>VUELTO / CAMBIO:</span>
                        <div class="text-right">
                            <span id="modalChangeUsdDisplay" class="font-mono block">$0.00 USD</span>
                            <span id="modalChangeVesDisplay" class="text-xs font-mono text-emerald-300/80">Bs. 0,00 VES</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Modal con Botón Confirmar Venta -->
            <div class="p-4 bg-[#1E1B4B] border-t border-white/10 flex items-center justify-between">
                <button type="button" onclick="closePaymentModal()" class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-xs font-bold text-white transition">
                    Cancelar [ESC]
                </button>
                <button type="button" id="btnSubmitSale" disabled
                    class="px-6 py-2.5 rounded-xl bg-[#F43F5E] hover:bg-[#F43F5E]/90 disabled:opacity-40 disabled:cursor-not-allowed text-white font-display font-black text-sm transition pulse-coral cursor-pointer">
                    Confirmar Cobro e Imprimir 🖨
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 2: DATOS DEL CLIENTE (F4)            -->
    <!-- ========================================== -->
    <div id="customerModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-[#121728] border border-white/15 rounded-3xl max-w-md w-full p-5 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                <h3 class="font-display font-black text-base text-white">Datos del Cliente (Cédula / RIF)</h3>
                <button type="button" onclick="closeCustomerModal()" class="text-slate-400 hover:text-white">✕</button>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <label class="block text-slate-400 mb-1">Cédula o RIF</label>
                    <input type="text" id="custDocInput" placeholder="Ej: V-12345678 o J-50000000-0"
                        class="w-full bg-[#1E1B4B] border border-white/15 rounded-xl px-3 py-2 text-white font-mono focus:outline-none focus:border-[#06B6D4]">
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">Nombre Completo / Razón Social</label>
                    <input type="text" id="custNameInput" placeholder="Ej: Juan Perez"
                        class="w-full bg-[#1E1B4B] border border-white/15 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-[#06B6D4]">
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">Teléfono</label>
                    <input type="text" id="custPhoneInput" placeholder="Ej: +584121234567"
                        class="w-full bg-[#1E1B4B] border border-white/15 rounded-xl px-3 py-2 text-white font-mono focus:outline-none focus:border-[#06B6D4]">
                </div>

                <button type="button" onclick="setQuickFinalConsumer()" class="text-[11px] text-[#06B6D4] hover:underline block pt-1">
                    ⚡ Establecer como Consumidor Final
                </button>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-white/10">
                <button type="button" onclick="closeCustomerModal()" class="px-4 py-2 rounded-xl bg-white/10 text-white text-xs font-bold">
                    Cerrar
                </button>
                <button type="button" onclick="saveCustomerData()" class="px-4 py-2 rounded-xl bg-[#06B6D4] text-slate-900 text-xs font-bold">
                    Guardar Datos [Enter]
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 3: CONTROL DE CAJA / CORTE X Y Z    -->
    <!-- ========================================== -->
    <div id="shiftModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-[#121728] border border-white/15 rounded-3xl max-w-lg w-full p-5 space-y-4 shadow-2xl text-xs">
            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                <h3 class="font-display font-black text-base text-white" id="shiftModalTitle">Control de Caja Registradora</h3>
                <button type="button" onclick="closeShiftModal()" class="text-slate-400 hover:text-white">✕</button>
            </div>

            <!-- Si no hay turno abierto: Formulario de Apertura -->
            <div id="openShiftForm" class="{{ $activeShift ? 'hidden' : '' }} space-y-3">
                <p class="text-slate-300">Indique los fondos iniciales para abrir el turno:</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-400 mb-1">Fondo Inicial ($ USD)</label>
                        <input type="number" step="0.01" min="0" id="openBalanceUsd" value="20.00"
                            class="w-full bg-[#1E1B4B] border border-white/15 rounded-xl px-3 py-2 text-white font-mono font-bold focus:outline-none focus:border-[#06B6D4]">
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1">Fondo Inicial (Bs VES)</label>
                        <input type="number" step="0.01" min="0" id="openBalanceVes" value="500.00"
                            class="w-full bg-[#1E1B4B] border border-white/15 rounded-xl px-3 py-2 text-white font-mono font-bold focus:outline-none focus:border-[#06B6D4]">
                    </div>
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">Notas de Apertura</label>
                    <input type="text" id="openShiftNotes" placeholder="Ej: Turno Mañana"
                        class="w-full bg-[#1E1B4B] border border-white/15 rounded-xl px-3 py-2 text-white">
                </div>
                <button type="button" onclick="submitOpenShift()" class="w-full py-2.5 rounded-xl bg-[#06B6D4] text-slate-900 font-bold text-sm cursor-pointer">
                    Abrir Turno Ahora
                </button>
            </div>

            <!-- Si hay turno abierto: Opciones de Corte X y Cierre Corte Z -->
            <div id="activeShiftControls" class="{{ $activeShift ? '' : 'hidden' }} space-y-4">
                <div class="p-3 rounded-2xl bg-black/40 border border-white/10 space-y-1.5">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Caja Registradora:</span>
                        <strong class="text-white">{{ $cashRegister->name }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Cajero Activo:</span>
                        <strong class="text-white">{{ $user->full_name }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Apertura:</span>
                        <strong class="text-slate-200">{{ $activeShift?->opened_at?->format('d/m/Y h:i A') }}</strong>
                    </div>
                </div>

                <!-- Botones de Acción de Corte -->
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="generateCorteX()" class="py-2.5 px-3 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold transition text-center cursor-pointer">
                        📊 Arqueo Corte X
                    </button>
                    <button type="button" onclick="showCloseShiftView()" class="py-2.5 px-3 rounded-xl bg-[#F43F5E] hover:bg-[#F43F5E]/90 text-white font-bold transition text-center cursor-pointer pulse-coral">
                        🔒 Cierre Corte Z
                    </button>
                </div>

                <!-- Formulario Oculto de Arqueo Físico para Corte Z -->
                <div id="closeShiftForm" class="hidden p-3 rounded-2xl bg-white/5 border border-white/10 space-y-3">
                    <span class="text-xs font-bold text-white block">Arqueo Físico (Conteo Real en Caja):</span>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-slate-400 mb-1">Efectivo Físico USD</label>
                            <input type="number" step="0.01" min="0" id="actualCashUsdInput" placeholder="0.00"
                                class="w-full bg-[#1E1B4B] border border-white/15 rounded-xl px-2.5 py-2 text-white font-mono font-bold focus:outline-none focus:border-[#06B6D4]">
                        </div>
                        <div>
                            <label class="block text-slate-400 mb-1">Efectivo Físico VES</label>
                            <input type="number" step="0.01" min="0" id="actualCashVesInput" placeholder="0.00"
                                class="w-full bg-[#1E1B4B] border border-white/15 rounded-xl px-2.5 py-2 text-white font-mono font-bold focus:outline-none focus:border-[#06B6D4]">
                        </div>
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1">Observaciones de Cierre</label>
                        <input type="text" id="closeNotesInput" placeholder="Ej: Arqueo conforme"
                            class="w-full bg-[#1E1B4B] border border-white/15 rounded-xl px-2.5 py-2 text-white">
                    </div>
                    <button type="button" onclick="submitCloseShift()" class="w-full py-2.5 rounded-xl bg-[#F43F5E] text-white font-bold text-xs cursor-pointer">
                        Confirmar Cierre Definitivo de Caja
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- DRAWER LATERAL: PEDIDOS WEB EN VIVO (JIHOVA)-->
    <!-- ========================================== -->
    <div id="onlineOrdersDrawer" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex justify-end">
        <div class="w-full max-w-md bg-[#121728] border-l border-white/15 h-full flex flex-col shadow-2xl">
            <!-- Header Drawer -->
            <div class="p-4 bg-[#1E1B4B] border-b border-white/10 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🛵</span>
                    <div>
                        <h3 class="font-display font-black text-base text-white">Pedidos Web En Vivo</h3>
                        <p class="text-[11px] text-slate-400">Canal jihova.app · Sucursal {{ $activeBranch->branch_name }}</p>
                    </div>
                </div>
                <button type="button" onclick="toggleOnlineOrdersDrawer()" class="text-slate-400 hover:text-white text-base">✕</button>
            </div>

            <!-- Lista de Pedidos Web -->
            <div id="onlineOrdersList" class="flex-1 overflow-y-auto p-4 space-y-3">
                <div class="p-6 text-center text-slate-400 text-xs">
                    Cargando pedidos online...
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DE ÉXITO Y RECIBO DE VENTA -->
    <div id="saleSuccessModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-[#121728] border border-white/15 rounded-3xl max-w-sm w-full p-6 text-center space-y-4 shadow-2xl">
            <div class="w-16 h-16 rounded-full bg-emerald-500/20 text-emerald-400 text-3xl flex items-center justify-center mx-auto border border-emerald-400/40">
                ✓
            </div>
            <h3 class="font-display font-black text-xl text-white">¡Venta Completada!</h3>
            <p class="text-xs text-slate-300" id="successOrderNumberDisplay">Orden generada con éxito.</p>
            <div class="flex flex-col gap-2 pt-2">
                <a id="btnReceiptThermal80" href="#" target="_blank" class="w-full py-2.5 rounded-xl bg-[#06B6D4] text-slate-900 font-bold text-xs transition">
                    🖨 Imprimir Recibo (80mm)
                </a>
                <a id="btnReceiptThermal58" href="#" target="_blank" class="w-full py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs transition">
                    🖨 Imprimir Recibo (58mm)
                </a>
                <button type="button" onclick="closeSaleSuccessModal()" class="w-full py-2 text-xs text-slate-400 hover:text-white">
                    Nueva Venta [ESC]
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MOTOR JAVASCRIPT REACTIVO DEL POS          -->
    <!-- ========================================== -->
    <script>
        // Configuración y variables de estado
        const CONFIG = {
            branchId: '{{ $activeBranch->id }}',
            storeId: '{{ $activeStore->id }}',
            shiftId: '{{ $activeShift?->id ?? "" }}',
            registerId: '{{ $cashRegister->id }}',
            bcvRate: parseFloat('{{ $bcvRate }}'),
            isSpecialTaxpayer: {{ $isSpecialTaxpayer ? 'true' : 'false' }},
            csrfToken: '{{ csrf_token() }}',
            routes: {
                saleStore: '{{ route("merchant.pos.store") }}',
                liveOrders: '{{ route("merchant.pos.live-orders") }}',
                shiftOpen: '{{ route("merchant.pos.shifts.open") }}',
                shiftClose: '{{ $activeShift ? route("merchant.pos.shifts.close", $activeShift) : "" }}',
                shiftCorteX: '{{ $activeShift ? route("merchant.pos.shifts.corte-x", $activeShift) : "" }}',
                receiptPrintBase: '{{ url("/panel-tienda/pos/imprimir/orden") }}'
            }
        };

        // Catálogo de productos desde el servidor
        const CATALOG = @json($catalog);

        // Estado del POS en Memoria
        let ticket = [];
        let payments = [];
        let customer = {
            doc: 'V-00000000',
            name: 'Consumidor Final',
            phone: ''
        };
        let activeCategory = 'all';
        let searchQuery = '';
        let liveOrdersPollingInterval = null;
        let lastLiveOrdersCount = 0;

        // Inicialización al cargar el DOM
        document.addEventListener('DOMContentLoaded', () => {
            initOfflineSync();
            renderCatalog();
            renderTicket();
            setupEventListeners();
            startLiveOrdersPolling();
        });

        // ==========================================
        // 1. RENDERIZADO DEL CATÁLOGO DE PRODUCTOS
        // ==========================================
        function renderCatalog() {
            const grid = document.getElementById('productGrid');
            grid.innerHTML = '';

            const filtered = CATALOG.filter(p => {
                const matchesCat = activeCategory === 'all' || p.category_id === activeCategory;
                const q = searchQuery.toLowerCase().trim();
                const matchesQuery = !q || p.name.toLowerCase().includes(q) || (p.sku && p.sku.toLowerCase().includes(q));
                return matchesCat && matchesQuery;
            });

            if (filtered.length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full py-16 text-center text-slate-400">
                        <span class="text-4xl block mb-2">🔍</span>
                        <p class="text-sm font-semibold">No se encontraron productos.</p>
                        <p class="text-xs text-slate-500">Pruebe con otro término o categoría.</p>
                    </div>
                `;
                return;
            }

            filtered.forEach(p => {
                const card = document.createElement('div');
                card.className = "bg-white rounded-2xl border border-slate-200 p-3 flex flex-col justify-between hover:shadow-md hover:border-[#06B6D4] transition cursor-pointer select-none group";
                card.onclick = () => addToTicket(p.id);

                const hasStock = p.stock > 0;
                const stockBadge = hasStock 
                    ? `<span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${p.stock <= 5 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700'}">${p.stock} en stock</span>`
                    : `<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">Agotado</span>`;

                card.innerHTML = `
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider truncate max-w-[100px]">${p.category_name}</span>
                            ${stockBadge}
                        </div>
                        <h4 class="text-xs font-bold text-slate-900 group-hover:text-[#06B6D4] transition line-clamp-2 leading-tight mb-1">
                            ${p.name}
                        </h4>
                        ${p.sku ? `<span class="text-[9px] font-mono text-slate-400 block">${p.sku}</span>` : ''}
                    </div>

                    <div class="mt-3 pt-2 border-t border-slate-100 flex items-baseline justify-between">
                        <div>
                            <span class="font-display font-black text-sm sm:text-base text-slate-900 font-mono">
                                $${p.price_usd.toFixed(2)}
                            </span>
                            <span class="text-[10px] text-slate-500 font-mono block">
                                Bs. ${p.price_ves.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                            </span>
                        </div>
                        <button type="button" class="w-7 h-7 rounded-lg bg-slate-100 group-hover:bg-[#06B6D4] group-hover:text-white text-slate-700 flex items-center justify-center font-bold text-sm transition">
                            +
                        </button>
                    </div>
                `;

                grid.appendChild(card);
            });
        }

        // ==========================================
        // 2. GESTIÓN DEL TICKET / CARRITO DE VENTA
        // ==========================================
        function addToTicket(productId, qty = 1) {
            const product = CATALOG.find(p => p.id === productId);
            if (!product) return;

            const existing = ticket.find(i => i.product_id === productId);
            if (existing) {
                existing.quantity += qty;
            } else {
                ticket.push({
                    product_id: product.id,
                    name: product.name,
                    sku: product.sku,
                    unit_price: product.price_usd,
                    quantity: qty
                });
            }

            renderTicket();
        }

        function updateTicketQty(productId, delta) {
            const item = ticket.find(i => i.product_id === productId);
            if (!item) return;

            item.quantity += delta;
            if (item.quantity <= 0) {
                removeFromTicket(productId);
            } else {
                renderTicket();
            }
        }

        function removeFromTicket(productId) {
            ticket = ticket.filter(i => i.product_id !== productId);
            renderTicket();
        }

        function clearTicket() {
            ticket = [];
            payments = [];
            renderTicket();
        }

        function calculateTotals() {
            const subtotalUsd = ticket.reduce((sum, item) => sum + (item.unit_price * item.quantity), 0);
            const subtotalVes = subtotalUsd * CONFIG.bcvRate;

            // Estimación de IGTF (si es contribuyente especial, 3% sobre la fracción de divisas)
            // En el ticket inicial, se muestra 0 o la estimación máxima
            const grandTotalUsd = subtotalUsd;
            const grandTotalVes = subtotalVes;

            return {
                subtotalUsd,
                subtotalVes,
                grandTotalUsd,
                grandTotalVes,
                itemCount: ticket.reduce((sum, item) => sum + item.quantity, 0)
            };
        }

        function renderTicket() {
            const list = document.getElementById('ticketItemsList');
            const totals = calculateTotals();

            document.getElementById('ticketItemCountBadge').textContent = `${totals.itemCount} ítems`;
            document.getElementById('ticketSubtotalUsd').textContent = `$${totals.subtotalUsd.toFixed(2)}`;
            document.getElementById('ticketSubtotalVes').textContent = `Bs. ${totals.subtotalVes.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            document.getElementById('ticketGrandTotalUsd').textContent = `$${totals.grandTotalUsd.toFixed(2)}`;
            document.getElementById('ticketGrandTotalVes').textContent = `≈ Bs. ${totals.grandTotalVes.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

            const checkoutBtn = document.getElementById('btnTriggerCheckout');
            checkoutBtn.disabled = ticket.length === 0 || !CONFIG.shiftId;

            if (ticket.length === 0) {
                list.innerHTML = `
                    <div class="h-full flex flex-col items-center justify-center text-center text-slate-500 p-6 space-y-3">
                        <span class="text-4xl">🛒</span>
                        <p class="text-xs">El ticket está vacío.<br>Escanee un código de barras o haga clic en un producto.</p>
                    </div>
                `;
                return;
            }

            list.innerHTML = '';
            ticket.forEach(item => {
                const row = document.createElement('div');
                row.className = "p-2.5 rounded-xl bg-white/5 border border-white/5 hover:border-white/10 flex items-center justify-between gap-2 text-xs";
                
                const itemTotalUsd = item.unit_price * item.quantity;
                const itemTotalVes = itemTotalUsd * CONFIG.bcvRate;

                row.innerHTML = `
                    <div class="flex-1 min-w-0">
                        <h5 class="font-bold text-white truncate">${item.name}</h5>
                        <div class="text-[10px] text-slate-400 font-mono">
                            $${item.unit_price.toFixed(2)} c/u
                        </div>
                    </div>

                    <!-- Stepper Cantidad -->
                    <div class="flex items-center gap-1 bg-black/30 rounded-lg p-0.5 border border-white/10 shrink-0">
                        <button type="button" onclick="updateTicketQty('${item.product_id}', -1)" class="w-6 h-6 rounded flex items-center justify-center text-slate-300 hover:text-white hover:bg-white/10 font-bold">-</button>
                        <span class="w-6 text-center font-bold text-white font-mono">${item.quantity}</span>
                        <button type="button" onclick="updateTicketQty('${item.product_id}', 1)" class="w-6 h-6 rounded flex items-center justify-center text-slate-300 hover:text-white hover:bg-white/10 font-bold">+</button>
                    </div>

                    <!-- Precio Total y Eliminar -->
                    <div class="text-right shrink-0 min-w-[65px]">
                        <span class="font-bold text-white font-mono block">$${itemTotalUsd.toFixed(2)}</span>
                        <span class="text-[9px] text-slate-400 font-mono block">Bs. ${itemTotalVes.toFixed(0)}</span>
                    </div>

                    <button type="button" onclick="removeFromTicket('${item.product_id}')" class="text-slate-500 hover:text-[#F43F5E] p-1 transition" title="Eliminar artículo">
                        ✕
                    </button>
                `;

                list.appendChild(row);
            });
        }

        // ==========================================
        // 3. MODAL DE COBRO MULTIMONEDA (F2)
        // ==========================================
        function openPaymentModal() {
            if (ticket.length === 0 || !CONFIG.shiftId) return;

            payments = [];
            const totals = calculateTotals();

            document.getElementById('modalTotalDueUsd').textContent = `$${totals.subtotalUsd.toFixed(2)}`;
            document.getElementById('modalTotalDueVes').textContent = `Bs. ${totals.subtotalVes.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

            // Precargar el monto faltante en el primer método (Efectivo USD)
            document.getElementById('payAmountInput').value = totals.subtotalUsd.toFixed(2);
            document.getElementById('payMethodSelect').value = 'efectivo_usd';
            updatePaymentCurrencyLabel();

            renderPaymentsBreakdown();
            document.getElementById('paymentModal').classList.remove('hidden');
            document.getElementById('payAmountInput').focus();
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').classList.add('hidden');
        }

        function updatePaymentCurrencyLabel() {
            const select = document.getElementById('payMethodSelect');
            const currency = select.selectedOptions[0].getAttribute('data-currency');
            document.getElementById('payCurrencyLabel').textContent = currency;

            // Si es bolívares, precargar el monto equivalente en VES
            const totals = calculateTotals();
            const paidUsd = payments.reduce((acc, p) => acc + (p.currency === 'USD' ? p.amount : p.amount / CONFIG.bcvRate), 0);
            const remainingUsd = Math.max(0, totals.subtotalUsd - paidUsd);

            if (currency === 'VES') {
                document.getElementById('payAmountInput').value = (remainingUsd * CONFIG.bcvRate).toFixed(2);
            } else {
                document.getElementById('payAmountInput').value = remainingUsd.toFixed(2);
            }
        }

        function addPaymentRow() {
            const methodSelect = document.getElementById('payMethodSelect');
            const method = methodSelect.value;
            const currency = methodSelect.selectedOptions[0].getAttribute('data-currency');
            const amount = parseFloat(document.getElementById('payAmountInput').value);
            const ref = document.getElementById('payRefInput').value.trim();

            if (isNaN(amount) || amount <= 0) {
                alert('Ingrese un monto válido mayor a 0.');
                return;
            }

            payments.push({
                method,
                currency,
                amount,
                reference_number: ref || null
            });

            document.getElementById('payRefInput').value = '';
            renderPaymentsBreakdown();

            // Preparar siguiente monto si falta
            updatePaymentCurrencyLabel();
        }

        function removePaymentRow(index) {
            payments.splice(index, 1);
            renderPaymentsBreakdown();
            updatePaymentCurrencyLabel();
        }

        function renderPaymentsBreakdown() {
            const table = document.getElementById('paymentsListTable');
            const totals = calculateTotals();
            const subtotalUsd = totals.subtotalUsd;

            // Calcular IGTF (3% únicamente sobre montos en divisas si isSpecialTaxpayer es true)
            let foreignCurrencyUsd = 0;
            let totalPaidUsdEquivalent = 0;

            payments.forEach(p => {
                const usdVal = p.currency === 'USD' ? p.amount : p.amount / CONFIG.bcvRate;
                totalPaidUsdEquivalent += usdVal;

                if (CONFIG.isSpecialTaxpayer && (p.method === 'efectivo_usd' || p.method === 'zelle')) {
                    foreignCurrencyUsd += usdVal;
                }
            });

            const igtfUsd = CONFIG.isSpecialTaxpayer ? (foreignCurrencyUsd * 0.03) : 0;
            const totalDueUsd = subtotalUsd + igtfUsd;

            // Actualizar indicadores
            document.getElementById('modalIgtfDisplay').textContent = `+$${igtfUsd.toFixed(2)}`;
            document.getElementById('modalTotalPaidDisplay').textContent = `$${totalPaidUsdEquivalent.toFixed(2)}`;

            const remainingDue = totalDueUsd - totalPaidUsdEquivalent;
            const submitBtn = document.getElementById('btnSubmitSale');

            if (remainingDue > 0.005) {
                // Aún falta por pagar
                document.getElementById('balanceDueContainer').classList.remove('hidden');
                document.getElementById('changeContainer').classList.add('hidden');
                document.getElementById('modalRemainingDueDisplay').textContent = `$${remainingDue.toFixed(2)} (Bs. ${(remainingDue * CONFIG.bcvRate).toFixed(2)})`;
                submitBtn.disabled = true;
            } else {
                // Pagado completo o con vuelto
                document.getElementById('balanceDueContainer').classList.add('hidden');
                const changeUsd = Math.abs(remainingDue);
                const changeVes = changeUsd * CONFIG.bcvRate;

                if (changeUsd > 0.005) {
                    document.getElementById('changeContainer').classList.remove('hidden');
                    document.getElementById('modalChangeUsdDisplay').textContent = `$${changeUsd.toFixed(2)} USD`;
                    document.getElementById('modalChangeVesDisplay').textContent = `Bs. ${changeVes.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} VES`;
                } else {
                    document.getElementById('changeContainer').classList.add('hidden');
                }

                submitBtn.disabled = false;
            }

            // Renderizar tabla de líneas de pago
            if (payments.length === 0) {
                table.innerHTML = '<div class="p-3 text-center text-slate-400">Aún no ha ingresado ningún método de pago.</div>';
                return;
            }

            table.innerHTML = '';
            payments.forEach((p, idx) => {
                const row = document.createElement('div');
                row.className = "p-2.5 flex items-center justify-between text-xs";
                
                const formattedAmount = p.currency === 'USD' 
                    ? `$${p.amount.toFixed(2)}` 
                    : `Bs. ${p.amount.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

                row.innerHTML = `
                    <div>
                        <strong class="text-white">${p.method.replace('_', ' ').toUpperCase()}</strong>
                        ${p.reference_number ? `<span class="text-[10px] text-slate-400 font-mono ml-1.5">Ref: ${p.reference_number}</span>` : ''}
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="font-bold text-[#06B6D4] font-mono">${formattedAmount}</span>
                        <button type="button" onclick="removePaymentRow(${idx})" class="text-slate-400 hover:text-rose-400 font-bold">✕</button>
                    </div>
                `;
                table.appendChild(row);
            });
        }

        // Enviar Venta al Servidor
        async function submitSale() {
            const submitBtn = document.getElementById('btnSubmitSale');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Procesando venta...';

            const payload = {
                branch_id: CONFIG.branchId,
                cash_shift_id: CONFIG.shiftId,
                customer_doc: customer.doc,
                customer_name: customer.name,
                customer_phone: customer.phone,
                items: ticket.map(i => ({
                    product_id: i.product_id,
                    quantity: i.quantity,
                    unit_price: i.unit_price
                })),
                payments: payments
            };

            // Tolerancia Offline PWA
            if (!navigator.onLine) {
                enqueueOfflineSale(payload);
                submitBtn.textContent = 'Confirmar Cobro e Imprimir 🖨';
                return;
            }

            try {
                const response = await fetch(CONFIG.routes.saleStore, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CONFIG.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    closePaymentModal();
                    showSaleSuccessModal(data.receipt);
                    clearTicket();
                } else {
                    alert(data.message || 'Error al procesar la venta. Verifique los datos.');
                }
            } catch (err) {
                console.error('Error de red al procesar venta:', err);
                if (confirm('Hubo un problema de conexión. ¿Desea guardar la venta en cola offline local para sincronizarla al volver internet?')) {
                    enqueueOfflineSale(payload);
                }
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Confirmar Cobro e Imprimir 🖨';
            }
        }

        function showSaleSuccessModal(receipt) {
            document.getElementById('successOrderNumberDisplay').textContent = `Orden: ${receipt.order_number} · Total: $${receipt.totals.total_due_usd.toFixed(2)}`;
            document.getElementById('btnReceiptThermal80').href = `${CONFIG.routes.receiptPrintBase}/${receipt.order_id}?format=80mm&autoprint=1`;
            document.getElementById('btnReceiptThermal58').href = `${CONFIG.routes.receiptPrintBase}/${receipt.order_id}?format=58mm&autoprint=1`;
            document.getElementById('saleSuccessModal').classList.remove('hidden');
        }

        function closeSaleSuccessModal() {
            document.getElementById('saleSuccessModal').classList.add('hidden');
            document.getElementById('barcodeSearchInput').focus();
        }

        // ==========================================
        // 4. CLIENTE (F4)
        // ==========================================
        function openCustomerModal() {
            document.getElementById('custDocInput').value = customer.doc;
            document.getElementById('custNameInput').value = customer.name;
            document.getElementById('custPhoneInput').value = customer.phone;
            document.getElementById('customerModal').classList.remove('hidden');
            document.getElementById('custDocInput').focus();
        }

        function closeCustomerModal() {
            document.getElementById('customerModal').classList.add('hidden');
        }

        function setQuickFinalConsumer() {
            document.getElementById('custDocInput').value = 'V-00000000';
            document.getElementById('custNameInput').value = 'Consumidor Final';
            document.getElementById('custPhoneInput').value = '';
        }

        function saveCustomerData() {
            customer.doc = document.getElementById('custDocInput').value.trim() || 'V-00000000';
            customer.name = document.getElementById('custNameInput').value.trim() || 'Consumidor Final';
            customer.phone = document.getElementById('custPhoneInput').value.trim();

            document.getElementById('ticketCustomerDisplay').textContent = `${customer.name} (${customer.doc})`;
            closeCustomerModal();
        }

        // ==========================================
        // 5. CONTROL DE CAJA Y ARQUEOS (CORTE X / Z)
        // ==========================================
        function openShiftModalAction() {
            document.getElementById('shiftModal').classList.remove('hidden');
        }

        function closeShiftModal() {
            document.getElementById('shiftModal').classList.add('hidden');
        }

        async function submitOpenShift() {
            const usd = parseFloat(document.getElementById('openBalanceUsd').value) || 0;
            const ves = parseFloat(document.getElementById('openBalanceVes').value) || 0;
            const notes = document.getElementById('openShiftNotes').value.trim();

            try {
                const response = await fetch(CONFIG.routes.shiftOpen, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CONFIG.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        register_id: CONFIG.registerId,
                        opening_balance_usd: usd,
                        opening_balance_ves: ves,
                        notes
                    })
                });

                const data = await response.json();
                if (response.ok && data.success) {
                    alert('Turno de caja abierto con éxito.');
                    window.location.reload();
                } else {
                    alert(data.message || 'Error al abrir caja.');
                }
            } catch (e) {
                alert('Error de conexión al abrir turno.');
            }
        }

        function showCloseShiftView() {
            document.getElementById('closeShiftForm').classList.toggle('hidden');
        }

        async function generateCorteX() {
            if (!CONFIG.routes.shiftCorteX) return;
            try {
                const response = await fetch(CONFIG.routes.shiftCorteX);
                const data = await response.json();
                if (data.success) {
                    const s = data.summary;
                    alert(`ARQUEO CORTE X (PARCIAL):\n\n` +
                          `Ventas Realizadas: ${s.total_sales_count}\n` +
                          `Total Ventas: $${s.total_sales_usd.toFixed(2)}\n` +
                          `Efectivo Esperado USD: $${s.expected_cash_usd.toFixed(2)}\n` +
                          `Efectivo Esperado VES: Bs. ${s.expected_cash_ves.toFixed(2)}`);
                }
            } catch (e) {
                alert('Error al generar Corte X');
            }
        }

        async function submitCloseShift() {
            if (!CONFIG.routes.shiftClose) return;
            const actualUsd = parseFloat(document.getElementById('actualCashUsdInput').value) || 0;
            const actualVes = parseFloat(document.getElementById('actualCashVesInput').value) || 0;
            const notes = document.getElementById('closeNotesInput').value.trim();

            if (!confirm('¿Confirma que desea cerrar este turno de caja de forma definitiva (Corte Z)?')) return;

            try {
                const response = await fetch(CONFIG.routes.shiftClose, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CONFIG.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        actual_cash_usd: actualUsd,
                        actual_cash_ves: actualVes,
                        closing_notes: notes
                    })
                });

                const data = await response.json();
                if (response.ok && data.success) {
                    const diffUsd = data.summary.difference_usd;
                    const diffVes = data.summary.difference_ves;
                    alert(`¡Caja Cerrada Exitosamente (Corte Z)!\n\n` +
                          `Diferencia USD: ${diffUsd >= 0 ? '+' : ''}$${diffUsd.toFixed(2)}\n` +
                          `Diferencia VES: ${diffVes >= 0 ? '+' : ''}Bs. ${diffVes.toFixed(2)}`);
                    window.location.reload();
                } else {
                    alert(data.message || 'Error al cerrar caja.');
                }
            } catch (e) {
                alert('Error de conexión al cerrar turno.');
            }
        }

        // ==========================================
        // 6. PEDIDOS WEB EN VIVO (POLLING REACTIVO)
        // ==========================================
        function toggleOnlineOrdersDrawer() {
            document.getElementById('onlineOrdersDrawer').classList.toggle('hidden');
        }

        function startLiveOrdersPolling() {
            fetchLiveOrders();
            liveOrdersPollingInterval = setInterval(fetchLiveOrders, 12000);
        }

        async function fetchLiveOrders() {
            if (!navigator.onLine) return;

            try {
                const response = await fetch(`${CONFIG.routes.liveOrders}?branch_id=${CONFIG.branchId}`);
                const data = await response.json();

                if (response.ok && data.success) {
                    const orders = data.orders || [];
                    const badge = document.getElementById('liveOrdersBadge');
                    
                    if (orders.length > 0) {
                        badge.textContent = orders.length;
                        badge.classList.remove('hidden');

                        // Si llegaron nuevos pedidos, emitir sonido sintetizado
                        if (orders.length > lastLiveOrdersCount) {
                            playNotificationSound();
                        }
                    } else {
                        badge.classList.add('hidden');
                    }
                    lastLiveOrdersCount = orders.length;
                    renderLiveOrdersList(orders);
                }
            } catch (e) {
                console.warn('Polling de pedidos online en pausa:', e);
            }
        }

        function renderLiveOrdersList(orders) {
            const list = document.getElementById('onlineOrdersList');
            if (orders.length === 0) {
                list.innerHTML = `
                    <div class="p-8 text-center text-slate-400 space-y-2">
                        <span class="text-4xl block">✨</span>
                        <p class="text-xs font-semibold">No hay pedidos web pendientes en este momento.</p>
                    </div>
                `;
                return;
            }

            list.innerHTML = '';
            orders.forEach(o => {
                const card = document.createElement('div');
                card.className = "p-3.5 rounded-2xl bg-white/5 border border-white/10 space-y-2.5 text-xs";
                
                card.innerHTML = `
                    <div class="flex items-center justify-between">
                        <strong class="text-[#06B6D4] font-mono text-sm">${o.sub_order_code}</strong>
                        <span class="px-2 py-0.5 rounded-md ${o.status_color} text-[10px] font-bold">
                            ${o.status_label}
                        </span>
                    </div>

                    <div>
                        <span class="font-bold text-white block">${o.customer.name}</span>
                        <span class="text-[11px] text-slate-400 block">📞 ${o.customer.phone}</span>
                        <span class="text-[11px] text-slate-400 block truncate">📍 ${o.delivery_address}</span>
                    </div>

                    <div class="p-2 rounded-xl bg-black/30 border border-white/5 space-y-1">
                        ${o.items.map(i => `
                            <div class="flex justify-between text-[11px]">
                                <span class="text-slate-300 font-bold">${i.quantity}x ${i.name}</span>
                                <span class="text-slate-400 font-mono">$${i.total_price.toFixed(2)}</span>
                            </div>
                        `).join('')}
                    </div>

                    <div class="flex items-center justify-between pt-1 border-t border-white/10">
                        <span class="font-bold text-white text-sm font-mono">$${o.subtotal_usd.toFixed(2)}</span>
                        <div class="flex items-center gap-1.5">
                            <a href="/panel-tienda/pos/imprimir/comanda/${o.sub_order_id}?autoprint=1" target="_blank"
                                class="px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-white font-bold text-[11px]">
                                🖨 Comanda
                            </a>
                            ${o.status === 'placed' ? `
                                <button type="button" onclick="advanceOrderStatus('${o.sub_order_id}', 'accepted_by_store')"
                                    class="px-2.5 py-1 rounded-lg bg-[#06B6D4] text-slate-900 font-bold text-[11px]">
                                    Aceptar
                                </button>
                            ` : o.status === 'accepted_by_store' ? `
                                <button type="button" onclick="advanceOrderStatus('${o.sub_order_id}', 'preparing')"
                                    class="px-2.5 py-1 rounded-lg bg-amber-500 text-slate-900 font-bold text-[11px]">
                                    Preparar
                                </button>
                            ` : ''}
                        </div>
                    </div>
                `;

                list.appendChild(card);
            });
        }

        async function advanceOrderStatus(subOrderId, nextStatus) {
            try {
                const response = await fetch(`/panel-tienda/pos/sub-pedidos/${subOrderId}/estado`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CONFIG.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: nextStatus })
                });

                if (response.ok) {
                    fetchLiveOrders();
                }
            } catch (e) {
                alert('Error al actualizar estado del pedido.');
            }
        }

        // Sintetizador de Sonido Web Audio API (Puro, sin archivos externos)
        function playNotificationSound() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();

                osc.type = 'sine';
                osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
                osc.frequency.setValueAtTime(880, ctx.currentTime + 0.15); // A5

                gain.gain.setValueAtTime(0.2, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.45);

                osc.connect(gain);
                gain.connect(ctx.destination);

                osc.start();
                osc.stop(ctx.currentTime + 0.45);
            } catch (e) {
                // Silencioso si el navegador bloquea audio sin interacción previa
            }
        }

        // ==========================================
        // 7. TOLERANCIA OFFLINE PWA
        // ==========================================
        function initOfflineSync() {
            window.addEventListener('online', () => {
                document.getElementById('networkStatusText').textContent = 'Online';
                document.getElementById('networkStatusBadge').className = 'flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-xs font-semibold';
                syncOfflineSales();
            });

            window.addEventListener('offline', () => {
                document.getElementById('networkStatusText').textContent = 'Offline (Local)';
                document.getElementById('networkStatusBadge').className = 'flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-amber-500/15 border border-amber-500/30 text-amber-400 text-xs font-semibold';
            });
        }

        function enqueueOfflineSale(payload) {
            const queue = JSON.parse(localStorage.getItem('pos_offline_sales') || '[]');
            payload._offline_timestamp = new Date().toISOString();
            queue.push(payload);
            localStorage.setItem('pos_offline_sales', JSON.stringify(queue));

            alert('Venta encolada exitosamente en memoria local offline. Se sincronizará automáticamente al restablecerse la conexión a internet.');
            closePaymentModal();
            clearTicket();
        }

        async function syncOfflineSales() {
            const queue = JSON.parse(localStorage.getItem('pos_offline_sales') || '[]');
            if (queue.length === 0) return;

            console.log(`Sincronizando ${queue.length} ventas offline pendientes...`);
            const pending = [...queue];
            localStorage.removeItem('pos_offline_sales');

            for (const sale of pending) {
                try {
                    await fetch(CONFIG.routes.saleStore, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CONFIG.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(sale)
                    });
                } catch (e) {
                    console.error('Error al sincronizar venta:', e);
                }
            }

            alert('¡Todas las ventas registradas durante el modo offline han sido sincronizadas con el servidor!');
        }

        // ==========================================
        // 8. EVENTOS DE TECLADO Y ESCÁNER USB
        // ==========================================
        function setupEventListeners() {
            // Buscador por código de barras USB
            const searchInput = document.getElementById('barcodeSearchInput');
            const clearSearchBtn = document.getElementById('btnClearSearch');

            searchInput.addEventListener('input', (e) => {
                searchQuery = e.target.value;
                clearSearchBtn.classList.toggle('hidden', !searchQuery);
                renderCatalog();
            });

            // Enter en el buscador: si coincide exactamente con un SKU o hay un solo resultado, agregar de inmediato
            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const q = searchInput.value.trim().toLowerCase();
                    if (!q) return;

                    const match = CATALOG.find(p => (p.sku && p.sku.toLowerCase() === q) || p.name.toLowerCase() === q);
                    if (match) {
                        addToTicket(match.id);
                        searchInput.value = '';
                        searchQuery = '';
                        clearSearchBtn.classList.add('hidden');
                        renderCatalog();
                    }
                }
            });

            clearSearchBtn.addEventListener('click', () => {
                searchInput.value = '';
                searchQuery = '';
                clearSearchBtn.classList.add('hidden');
                renderCatalog();
                searchInput.focus();
            });

            // Filtro por Categorías
            document.querySelectorAll('.category-pill').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelectorAll('.category-pill').forEach(b => {
                        b.classList.remove('bg-[#1E1B4B]', 'text-white');
                        b.classList.add('bg-slate-100', 'text-slate-600');
                    });
                    btn.classList.remove('bg-slate-100', 'text-slate-600');
                    btn.classList.add('bg-[#1E1B4B]', 'text-white');
                    activeCategory = btn.getAttribute('data-category');
                    renderCatalog();
                });
            });

            // Atajos Globales de Teclado
            window.addEventListener('keydown', (e) => {
                // F2: Abrir modal de cobro
                if (e.key === 'F2') {
                    e.preventDefault();
                    if (!document.getElementById('paymentModal').classList.contains('hidden')) {
                        submitSale();
                    } else {
                        openPaymentModal();
                    }
                }
                // F4: Modal de cliente
                if (e.key === 'F4') {
                    e.preventDefault();
                    openCustomerModal();
                }
                // ESC: Cancelar modal o limpiar ticket
                if (e.key === 'Escape') {
                    if (!document.getElementById('paymentModal').classList.contains('hidden')) {
                        closePaymentModal();
                    } else if (!document.getElementById('customerModal').classList.contains('hidden')) {
                        closeCustomerModal();
                    } else if (!document.getElementById('shiftModal').classList.contains('hidden')) {
                        closeShiftModal();
                    } else if (!document.getElementById('saleSuccessModal').classList.contains('hidden')) {
                        closeSaleSuccessModal();
                    } else if (!document.getElementById('onlineOrdersDrawer').classList.contains('hidden')) {
                        toggleOnlineOrdersDrawer();
                    } else {
                        if (confirm('¿Desea limpiar el ticket actual?')) {
                            clearTicket();
                        }
                    }
                }
            });

            // Listeners de Botones
            document.getElementById('btnTriggerCheckout').addEventListener('click', openPaymentModal);
            document.getElementById('btnClearTicket').addEventListener('click', () => {
                if (confirm('¿Limpiar todos los productos del ticket?')) clearTicket();
            });
            document.getElementById('btnCustomerModal').addEventListener('click', openCustomerModal);
            document.getElementById('btnToggleOnlineOrders').addEventListener('click', toggleOnlineOrdersDrawer);
            document.getElementById('payMethodSelect').addEventListener('change', updatePaymentCurrencyLabel);
            document.getElementById('btnAddPaymentRow').addEventListener('click', addPaymentRow);
            document.getElementById('btnSubmitSale').addEventListener('click', submitSale);

            const openShiftTrigger = document.getElementById('btnTriggerOpenShift');
            if (openShiftTrigger) openShiftTrigger.addEventListener('click', openShiftModalAction);

            const openShiftModalBtn = document.getElementById('btnOpenShiftModal');
            if (openShiftModalBtn) openShiftModalBtn.addEventListener('click', openShiftModalAction);
        }
    </script>
</body>
</html>
