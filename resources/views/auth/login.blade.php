<x-layouts.mall title="Iniciar Sesión · JIHOVA">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
        <div class="md:col-span-6 bg-[#121728]/95 rounded-3xl border border-white/10 p-6 sm:p-8 shadow-2xl space-y-6">
            <div>
                <x-brand-logo size="sm" subtitle="El Primer Centro Comercial Online de Venezuela" class="mb-4" />
                <h1 class="font-display text-2xl font-extrabold text-white">Ingresa a JIHOVA</h1>
                <p class="text-sm text-slate-400 mt-1">Compra en tus tiendas favoritas, gestiona tu comercio o realiza entregas.</p>
            </div>

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Correo electrónico</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-4 py-2.5 rounded-xl border border-white/15 bg-[#0B0E17] text-white text-sm focus:outline-none focus:border-[#00F0FF]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Contraseña</label>
                    <input type="password" name="password" required class="w-full px-4 py-2.5 rounded-xl border border-white/15 bg-[#0B0E17] text-white text-sm focus:outline-none focus:border-[#00F0FF]">
                </div>
                <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-white/25 bg-[#0B0E17] accent-[#FF3366]">
                    <span>Mantener sesión iniciada</span>
                </label>
                <button type="submit" class="w-full py-3 rounded-2xl bg-[#FF3366] hover:bg-[#ff4d7a] text-white text-sm font-bold shadow-lg shadow-[#FF3366]/30 transition cursor-pointer">
                    Iniciar sesión
                </button>
            </form>

            <p class="text-xs text-center text-slate-400">
                ¿Aún no tienes cuenta? <a href="{{ route('register') }}" class="font-bold text-[#00F0FF] hover:underline">Regístrate aquí</a>
            </p>
        </div>

        @if ($demoEnabled)
            <div class="md:col-span-6 bg-[#121728]/95 text-white rounded-3xl overflow-hidden border border-[#6C28FE]/45 shadow-[0_24px_60px_-20px_rgba(108,40,254,0.5)]">
                <div class="relative h-40 overflow-hidden">
                    <img src="/images/catalog/hero-mall.jpg" alt="JIHOVA Mall" class="w-full h-full object-cover opacity-55 animate-ken-burns">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#121728] via-[#121728]/55 to-transparent"></div>
                    <img src="/images/catalog/jihova-brand-logo.jpg" alt="Logo JIHOVA" class="absolute top-3 right-3 w-14 h-14 rounded-2xl object-cover border border-white/20 shadow-lg">
                    <div class="absolute bottom-3 left-6 right-6">
                        <span class="px-2.5 py-1 rounded-full bg-[#00F0FF]/15 border border-[#00F0FF]/40 text-[#00F0FF] text-[11px] font-bold">Modo Demostración Local</span>
                        <h2 class="font-display text-xl font-bold mt-1">Acceso rápido por rol (1 clic)</h2>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    <p class="text-xs text-slate-400">Prueba el flujo completo desde que el cliente compra un desayuno y zapatos, la tienda los prepara y el repartidor de la plataforma los entrega.</p>

                    <div class="space-y-2.5">
                    @foreach ([
                        ['email' => 'cliente@jihova.test', 'role' => '🛍️ Cliente · Sofía Paredes', 'desc' => 'Explora tiendas, paga en checkout y rastrea entregas.'],
                        ['email' => 'comercio@jihova.test', 'role' => '🏪 Comerciante · Aurora Café & StepUp', 'desc' => 'Recibe pedidos, cambia estados y gestiona stock.'],
                        ['email' => 'repartidor@jihova.test', 'role' => '🛵 Repartidor · Carlos Bolívar', 'desc' => 'Toma pedidos listos, actualiza ruta GPS y entrega.'],
                        ['email' => 'admin@jihova.test', 'role' => '🛡️ Admin del Mall · Valentina Rojas', 'desc' => 'Aprueba tiendas, supervisa ventas y asigna delivery.'],
                    ] as $demo)
                        <form action="{{ route('demo.login') }}" method="POST">
                            @csrf
                            <input type="hidden" name="email" value="{{ $demo['email'] }}">
                            <button type="submit" class="w-full text-left p-3.5 rounded-2xl bg-[#0B0E17]/75 hover:bg-[#6C28FE]/25 border border-white/10 hover:border-[#00F0FF]/55 transition cursor-pointer">
                                <span class="block text-sm font-bold text-white">{{ $demo['role'] }}</span>
                                <span class="block text-xs text-slate-300 mt-0.5">{{ $demo['desc'] }}</span>
                            </button>
                        </form>
                    @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layouts.mall>
