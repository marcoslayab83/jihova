@props([
    'title' => 'JIHOVA · El Primer Centro Comercial Online de Venezuela',
])
<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0B0E17">
    <title>{{ $title }}</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 88 56'%3E%3Crect width='88' height='56' rx='14' fill='%230B0E17'/%3E%3Cellipse cx='44' cy='28' rx='14' ry='21' fill='none' stroke='%236C28FE' stroke-width='5'/%3E%3Cpath d='M23 14C14 14 7 20 7 28s7 14 16 14c11 0 31-28 42-28 9 0 16 6 16 14s-7 14-16 14C54 42 34 14 23 14Z' fill='none' stroke='%2300F0FF' stroke-width='6' stroke-linecap='round'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|syne:600,700,800" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
        <style>
            html { color-scheme: dark; background-color: #0B0E17; }
            body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; background-color: #0B0E17; color: #F3F4F6; }
            .font-display { font-family: 'Syne', 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }
            @keyframes kenBurnsSlow {
                0% { transform: scale(1) translate3d(0, 0, 0); }
                50% { transform: scale(1.08) translate3d(-1.5%, -1%, 0); }
                100% { transform: scale(1) translate3d(0, 0, 0); }
            }
            @keyframes floatSlow {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-6px); }
            }
            .animate-ken-burns { animation: kenBurnsSlow 18s ease-in-out infinite; }
            .animate-float-slow { animation: floatSlow 5s ease-in-out infinite; }
            .jihova-gradient-text {
                background: linear-gradient(90deg, #00F0FF 0%, #7C5CFF 52%, #6C28FE 100%);
                -webkit-background-clip: text;
                background-clip: text;
                color: transparent;
            }
        </style>
    @endif
</head>
<body class="bg-[#0B0E17] text-[#F3F4F6] min-h-screen flex flex-col antialiased selection:bg-[#FF3366] selection:text-white pb-20 md:pb-0 relative overflow-x-hidden">

    <!-- Atmósfera Lumínica JIHOVA -->
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden" aria-hidden="true">
        <div class="absolute -top-48 -left-32 w-[520px] h-[520px] rounded-full bg-[#6C28FE]/18 blur-[140px]"></div>
        <div class="absolute top-1/3 -right-40 w-[460px] h-[460px] rounded-full bg-[#00F0FF]/12 blur-[140px]"></div>
        <div class="absolute -bottom-48 left-1/3 w-[480px] h-[480px] rounded-full bg-[#FF3366]/10 blur-[150px]"></div>
    </div>

    <!-- Navbar Principal del Centro Comercial -->
    <header class="sticky top-0 z-40 bg-[#0B0E17]/85 backdrop-blur-2xl border-b border-white/10 shadow-[0_12px_32px_-16px_rgba(0,0,0,0.85)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="h-18 flex items-center justify-between gap-4 py-2.5">
                <!-- Logo y Ubicación -->
                <div class="flex items-center gap-4 min-w-0">
                    <a href="{{ route('mall.home') }}" class="flex items-center shrink-0 group transition hover:opacity-95">
                        <x-brand-logo size="md" subtitle="El Centro Comercial Online" class="hidden sm:inline-flex" />
                        <x-brand-logo size="sm" :show-text="true" class="sm:hidden" />
                    </a>

                    <button type="button" data-open-location-modal class="hidden md:flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#121728] border border-white/10 hover:border-[#00F0FF]/50 text-xs font-medium text-slate-200 transition max-w-[240px] cursor-pointer">
                        <span class="text-[#00F0FF] shrink-0">📍</span>
                        <span class="truncate">{{ $sharedLocation['label'] ?? 'Seleccionar ubicación' }}</span>
                        <span class="text-slate-400">▾</span>
                    </button>
                </div>

                <!-- Buscador Global -->
                <form action="{{ route('mall.explore') }}" method="GET" class="flex-1 max-w-xl hidden lg:flex items-center relative">
                    <input
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Busca desde un desayuno hasta un par de zapatos..."
                        class="w-full pl-4 pr-26 py-2.5 rounded-full bg-[#121728]/90 border border-white/15 text-sm text-[#F3F4F6] placeholder:text-slate-400 focus:outline-none focus:bg-[#171E33] focus:border-[#00F0FF] focus:ring-2 focus:ring-[#00F0FF]/20 transition"
                    >
                    <button type="submit" class="absolute right-1.5 px-4 py-1.5 rounded-full bg-[#FF3366] hover:bg-[#ff4d7a] text-white text-xs font-bold shadow-md shadow-[#FF3366]/30 transition cursor-pointer">
                        Buscar
                    </button>
                </form>

                <!-- Navegación derecha -->
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <a href="{{ route('mall.stores') }}" class="hidden sm:inline-flex px-3 py-2 rounded-xl text-sm font-semibold text-slate-300 hover:text-[#00F0FF] hover:bg-white/5 transition">
                        Tiendas
                    </a>

                    @auth
                        @if (auth()->user()->isMerchant())
                            <a href="{{ route('merchant.dashboard') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs sm:text-sm font-bold bg-[#6C28FE]/20 border border-[#6C28FE]/45 text-violet-200 hover:bg-[#6C28FE]/35 transition">
                                🏪 Mi Tienda
                            </a>
                        @elseif (auth()->user()->isDriver())
                            <a href="{{ route('driver.dashboard') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs sm:text-sm font-bold bg-[#00F0FF]/15 border border-[#00F0FF]/40 text-[#00F0FF] hover:bg-[#00F0FF]/25 transition">
                                🛵 Panel Delivery
                            </a>
                        @elseif (auth()->user()->isPlatformAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs sm:text-sm font-bold bg-gradient-to-r from-[#6C28FE]/30 to-[#00F0FF]/20 border border-[#00F0FF]/40 text-white hover:border-[#00F0FF] transition">
                                🛡️ Admin Mall
                            </a>
                        @endif

                        <a href="{{ route('orders.index') }}" class="hidden md:inline-flex px-3 py-2 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-white/5 transition">
                            Mis Pedidos
                        </a>

                        <div class="relative" id="user-menu-wrapper">
                            <button type="button" data-user-menu-btn class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-white/15 bg-[#121728] hover:border-[#00F0FF]/50 text-xs sm:text-sm font-semibold text-[#F3F4F6] transition cursor-pointer">
                                <span class="w-6 h-6 rounded-full bg-gradient-to-br from-[#6C28FE] to-[#00F0FF] text-white flex items-center justify-center font-bold text-xs shadow-sm">
                                    {{ mb_substr(auth()->user()->full_name, 0, 1) }}
                                </span>
                                <span class="hidden xl:inline max-w-[110px] truncate">{{ auth()->user()->full_name }}</span>
                                <span class="text-slate-400 text-[10px]">▾</span>
                            </button>
                            <div id="user-menu-dropdown" class="hidden absolute right-0 mt-2 w-68 bg-[#121728]/95 backdrop-blur-2xl rounded-2xl shadow-2xl border border-white/15 p-2 z-50">
                                <div class="px-3 py-2 border-b border-white/10 mb-1">
                                    <p class="text-xs font-bold text-white truncate">{{ auth()->user()->full_name }}</p>
                                    <p class="text-[11px] text-[#00F0FF] font-semibold">{{ auth()->user()->role->label() }}</p>
                                </div>
                                <a href="{{ route('orders.index') }}" class="block px-3 py-2 rounded-xl text-sm text-slate-200 hover:bg-white/10 hover:text-white">Mis pedidos</a>
                                <a href="{{ route('addresses.index') }}" class="block px-3 py-2 rounded-xl text-sm text-slate-200 hover:bg-white/10 hover:text-white">Mis direcciones</a>
                                @if (auth()->user()->isPlatformAdmin())
                                    <a href="{{ route('merchant.dashboard') }}" class="block px-3 py-2 rounded-xl text-sm text-slate-200 hover:bg-white/10 hover:text-white">Panel Comercios</a>
                                    <a href="{{ route('driver.dashboard') }}" class="block px-3 py-2 rounded-xl text-sm text-slate-200 hover:bg-white/10 hover:text-white">Panel Repartidores</a>
                                @endif
                                @if (app()->environment(['local', 'testing']))
                                    <div class="mt-1 pt-2 border-t border-white/10 space-y-1">
                                        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Cambiar rol demo</p>
                                        @foreach ([
                                            'cliente@jihova.test' => '🛍️ Cliente',
                                            'comercio@jihova.test' => '🏪 Tienda',
                                            'repartidor@jihova.test' => '🛵 Repartidor',
                                            'admin@jihova.test' => '🛡️ Admin Mall',
                                        ] as $demoEmail => $demoLabel)
                                            <form action="{{ route('demo.login') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="email" value="{{ $demoEmail }}">
                                                <button type="submit" class="w-full text-left px-3 py-1.5 rounded-xl text-xs font-semibold transition cursor-pointer {{ auth()->user()->email === $demoEmail ? 'bg-[#6C28FE]/30 border border-[#00F0FF]/40 text-[#00F0FF]' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                                                    {{ $demoLabel }}
                                                </button>
                                            </form>
                                        @endforeach
                                    </div>
                                @endif
                                <form action="{{ route('logout') }}" method="POST" class="mt-1 pt-1 border-t border-white/10">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-3 py-2 rounded-xl text-sm text-[#FF3366] hover:bg-[#FF3366]/15 font-semibold cursor-pointer">
                                        Cerrar sesión
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex px-3.5 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:text-[#00F0FF] hover:bg-white/5 transition">
                            Ingresar
                        </a>
                        <a href="{{ route('register') }}" class="hidden sm:inline-flex px-4 py-2 rounded-xl text-sm font-bold bg-[#6C28FE] hover:bg-[#7d42ff] text-white shadow-md shadow-[#6C28FE]/30 transition">
                            Crear cuenta
                        </a>
                    @endauth

                    <!-- Botón Carrito -->
                    <button type="button" data-open-cart class="relative inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-[#FF3366] hover:bg-[#ff4d7a] text-white text-sm font-bold shadow-lg shadow-[#FF3366]/30 transition cursor-pointer">
                        <span>🛒</span>
                        <span class="hidden sm:inline">Carrito</span>
                        <span data-cart-badge class="px-1.5 py-0.5 rounded-full bg-[#0B0E17] text-[#00F0FF] text-xs font-extrabold leading-none border border-[#00F0FF]/40 {{ ($sharedCartCount ?? 0) > 0 ? '' : 'hidden' }}">
                            {{ $sharedCartCount ?? 0 }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Buscador móvil -->
            <form action="{{ route('mall.explore') }}" method="GET" class="pb-3 lg:hidden flex gap-2">
                <input
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Buscar desayuno, zapatos, tiendas..."
                    class="flex-1 px-4 py-2 rounded-full bg-[#121728] border border-white/15 text-sm text-[#F3F4F6] placeholder:text-slate-400 focus:outline-none focus:border-[#00F0FF]"
                >
                <button type="submit" class="px-4 py-2 rounded-full bg-[#FF3366] text-white text-xs font-bold">Ir</button>
            </form>
        </div>
    </header>

    <!-- Mensajes Flash -->
    @if (session('status'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            <div class="bg-emerald-500/15 border border-emerald-400/35 text-emerald-200 px-4 py-3 rounded-2xl text-sm font-medium flex items-center justify-between gap-4 backdrop-blur-md">
                <span>✅ {{ session('status') }}</span>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            <div class="bg-[#FF3366]/15 border border-[#FF3366]/40 text-rose-200 px-4 py-3 rounded-2xl text-sm backdrop-blur-md">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Contenido Principal -->
    <main class="flex-1">
        {{ $slot }}
    </main>

    <!-- Footer del Centro Comercial -->
    <footer class="mt-20 bg-[#070910] text-slate-300 border-t border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="space-y-3 md:col-span-2">
                <x-brand-logo size="lg" subtitle="El Primer Centro Comercial Online de Venezuela" />
                <p class="text-sm text-slate-400 max-w-md pt-1 leading-relaxed">
                    Conectividad comercial sin límites: restaurantes, zapaterías, tecnología, farmacia y mercado en un solo checkout con delivery propio en tiempo real.
                </p>
                <div class="flex flex-wrap gap-2 pt-1 text-[11px] font-semibold">
                    <span class="px-2.5 py-1 rounded-full bg-[#6C28FE]/15 border border-[#6C28FE]/35 text-violet-300">Electric Violet</span>
                    <span class="px-2.5 py-1 rounded-full bg-[#00F0FF]/10 border border-[#00F0FF]/30 text-[#00F0FF]">Cyber Cyan Live Tracking</span>
                    <span class="px-2.5 py-1 rounded-full bg-[#FF3366]/15 border border-[#FF3366]/35 text-[#FF85A3]">Checkout Unificado</span>
                </div>
            </div>
            <div class="space-y-2.5 text-sm">
                <h4 class="font-display font-bold text-white">Centro Comercial</h4>
                <ul class="space-y-2 text-slate-400">
                    <li><a href="{{ route('mall.explore') }}" class="hover:text-[#00F0FF] transition">Explorar catálogo</a></li>
                    <li><a href="{{ route('mall.stores') }}" class="hover:text-[#00F0FF] transition">Directorio de tiendas</a></li>
                    <li><a href="{{ route('cart.index') }}" class="hover:text-[#00F0FF] transition">Mi carrito</a></li>
                    <li><a href="{{ route('mall.progress') }}" class="hover:text-[#00F0FF] transition">Arquitectura &amp; Progreso</a></li>
                </ul>
            </div>
            <div class="space-y-2.5 text-sm">
                <h4 class="font-display font-bold text-white">Operación y API</h4>
                <ul class="space-y-2 text-slate-400">
                    <li><a href="{{ route('login') }}" class="hover:text-[#00F0FF] transition">Acceso Tiendas y Delivery</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-[#00F0FF] transition">Digitaliza tu vitrina</a></li>
                    <li><a href="{{ url('/api/v1/health') }}" class="hover:text-[#00F0FF] transition">Estado API v1</a></li>
                </ul>
            </div>
        </div>
    </footer>

    <!-- Barra Inferior App Móvil -->
    <nav class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-[#0E1322]/95 backdrop-blur-xl border-t border-white/10 px-3 py-2 flex items-center justify-around text-[11px] font-semibold text-slate-300">
        <a href="{{ route('mall.home') }}" class="flex flex-col items-center gap-0.5 {{ request()->routeIs('mall.home') ? 'text-[#00F0FF]' : '' }}">
            <span class="text-base">🏠</span>
            <span>Inicio</span>
        </a>
        <a href="{{ route('mall.explore') }}" class="flex flex-col items-center gap-0.5 {{ request()->routeIs('mall.explore') ? 'text-[#00F0FF]' : '' }}">
            <span class="text-base">🔎</span>
            <span>Explorar</span>
        </a>
        <a href="{{ route('mall.stores') }}" class="flex flex-col items-center gap-0.5 {{ request()->routeIs('mall.stores*') ? 'text-[#00F0FF]' : '' }}">
            <span class="text-base">🏬</span>
            <span>Tiendas</span>
        </a>
        <a href="{{ auth()->check() ? route('orders.index') : route('login') }}" class="flex flex-col items-center gap-0.5 {{ request()->routeIs('orders.*') ? 'text-[#00F0FF]' : '' }}">
            <span class="text-base">📦</span>
            <span>{{ auth()->check() ? 'Pedidos' : 'Ingresar' }}</span>
        </a>
        <button type="button" data-open-cart class="relative flex flex-col items-center gap-0.5 text-[#FF3366] cursor-pointer">
            <span class="text-base">🛒</span>
            <span>Carrito</span>
        </button>
    </nav>

    <!-- Toast Container -->
    <div id="mall-toast-container" class="fixed bottom-20 md:bottom-5 right-5 z-50 flex flex-col gap-2"></div>

    <!-- Modal Selector de Ubicación -->
    <div id="location-modal" class="hidden fixed inset-0 z-50 bg-black/75 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-[#121728] rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-white/15 space-y-5 text-[#F3F4F6]">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-display text-lg font-bold text-white">¿Dónde entregamos tu pedido?</h3>
                    <p class="text-xs text-slate-400">Calculamos tiempos y tiendas cercanas según tu ubicación.</p>
                </div>
                <button type="button" data-close-location-modal class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white font-bold cursor-pointer">✕</button>
            </div>

            <div class="space-y-2">
                <p class="text-xs font-bold uppercase tracking-wider text-[#00F0FF]">Zonas rápidas</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach ($sharedPresetZones ?? [] as $zone)
                        <form action="{{ route('mall.location.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="zone_key" value="{{ $zone['key'] }}">
                            <button type="submit" class="w-full text-left px-3.5 py-2.5 rounded-xl border border-white/10 bg-[#0B0E17]/70 hover:border-[#00F0FF] hover:bg-[#00F0FF]/10 text-xs font-semibold text-slate-200 transition cursor-pointer">
                                📍 {{ $zone['label'] }}
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>

            <form id="location-custom-form" action="{{ route('mall.location.update') }}" method="POST" class="space-y-3 pt-3 border-t border-white/10">
                @csrf
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Coordenadas personalizadas</p>
                    <button type="button" id="use-browser-gps-btn" class="text-xs font-bold text-[#00F0FF] hover:text-cyan-200 cursor-pointer">📍 Usar mi GPS</button>
                </div>
                <input type="text" name="label" placeholder="Ej. Mi casa / Oficina" class="w-full px-3.5 py-2.5 rounded-xl border border-white/15 bg-[#0B0E17] text-sm text-white placeholder:text-slate-500 focus:outline-none focus:border-[#00F0FF]">
                <div class="grid grid-cols-2 gap-2">
                    <input type="number" step="0.000001" name="latitude" value="{{ $sharedLocation['latitude'] ?? 10.4880 }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-white/15 bg-[#0B0E17] text-sm text-white focus:outline-none focus:border-[#00F0FF]">
                    <input type="number" step="0.000001" name="longitude" value="{{ $sharedLocation['longitude'] ?? -66.8792 }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-white/15 bg-[#0B0E17] text-sm text-white focus:outline-none focus:border-[#00F0FF]">
                </div>
                <button type="submit" class="w-full py-3 rounded-xl bg-[#FF3366] hover:bg-[#ff4d7a] text-white text-sm font-bold shadow-lg shadow-[#FF3366]/25 transition cursor-pointer">
                    Guardar ubicación
                </button>
            </form>
        </div>
    </div>

    <!-- Drawer Lateral del Carrito -->
    <div id="cart-drawer" class="hidden fixed inset-0 z-50 bg-black/75 backdrop-blur-sm flex justify-end">
        <div class="flex-1" data-close-cart></div>
        <aside class="w-full max-w-md bg-[#0E1322] border-l border-white/10 h-full shadow-2xl flex flex-col text-[#F3F4F6]">
            <div class="px-5 py-4 border-b border-white/10 flex items-center justify-between">
                <div>
                    <h3 class="font-display text-lg font-bold text-white">Tu Carrito del Mall</h3>
                    <p class="text-xs text-slate-400">Agrupado automáticamente por tienda</p>
                </div>
                <button type="button" data-close-cart class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white font-bold cursor-pointer">✕</button>
            </div>

            <div id="cart-drawer-body" class="flex-1 overflow-y-auto p-5">
                <p class="text-sm text-slate-400 text-center py-8">Cargando carrito...</p>
            </div>

            <div id="cart-drawer-footer" class="hidden p-5 border-t border-white/10 bg-[#121728] space-y-3">
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between text-slate-300">
                        <span>Artículos</span>
                        <span id="cart-drawer-items-total">$0.00</span>
                    </div>
                    <div class="flex justify-between text-slate-300">
                        <span>Delivery &amp; servicio</span>
                        <span id="cart-drawer-delivery-total">$0.00</span>
                    </div>
                    <div class="flex justify-between text-base font-extrabold text-white pt-2 border-t border-white/10">
                        <span>Total estimado</span>
                        <span id="cart-drawer-grand-total" class="text-[#00F0FF]">$0.00</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('cart.index') }}" class="text-center py-2.5 rounded-xl border border-white/15 bg-white/5 hover:bg-white/10 text-sm font-semibold text-white transition">
                        Ver carrito
                    </a>
                    <a href="{{ route('checkout.show') }}" class="text-center py-2.5 rounded-xl bg-[#FF3366] hover:bg-[#ff4d7a] text-white text-sm font-bold shadow-lg shadow-[#FF3366]/30 transition">
                        Comprar Ahora
                    </a>
                </div>
            </div>
        </aside>
    </div>

    @if (! file_exists(public_path('build/manifest.json')) && ! file_exists(public_path('hot')))
        <script src="{{ asset('js/mall-app.js') }}"></script>
    @endif

    @stack('scripts')
</body>
</html>
