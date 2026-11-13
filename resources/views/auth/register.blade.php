<x-layouts.mall title="Crear Cuenta · JIHOVA">
    <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-[#121728]/95 rounded-3xl border border-white/10 p-6 sm:p-8 shadow-2xl space-y-6">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-[#00F0FF]">Únete al Mall Digital</p>
                <h1 class="font-display text-2xl font-extrabold text-white mt-1">Digitaliza tu vitrina o compra hoy</h1>
                <p class="text-sm text-slate-400 mt-1">Regístrate como cliente, abre tu tienda online o súmate como repartidor.</p>
            </div>

            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Tipo de cuenta</label>
                    <select name="account_type" id="register-account-type" class="w-full px-4 py-2.5 rounded-xl border border-white/15 bg-[#0B0E17] text-white text-sm focus:outline-none focus:border-[#00F0FF]">
                        <option value="customer" @selected(old('account_type', 'customer') === 'customer')>🛍️ Cliente (Comprar en el Mall)</option>
                        <option value="merchant_admin" @selected(old('account_type') === 'merchant_admin')>🏪 Comerciante (Abrir mi tienda online)</option>
                        <option value="driver" @selected(old('account_type') === 'driver')>🛵 Repartidor (Delivery de la plataforma)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nombre completo</label>
                    <input type="text" name="full_name" value="{{ old('full_name') }}" required class="w-full px-4 py-2.5 rounded-xl border border-white/15 bg-[#0B0E17] text-white text-sm focus:outline-none focus:border-[#00F0FF]">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Correo electrónico</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-4 py-2.5 rounded-xl border border-white/15 bg-[#0B0E17] text-white text-sm focus:outline-none focus:border-[#00F0FF]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Teléfono</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+58 412 000 0000" required class="w-full px-4 py-2.5 rounded-xl border border-white/15 bg-[#0B0E17] text-white placeholder:text-slate-500 text-sm focus:outline-none focus:border-[#00F0FF]">
                    </div>
                </div>

                <div id="merchant-fields" class="hidden bg-[#6C28FE]/15 p-4 rounded-2xl border border-[#6C28FE]/40">
                    <label class="block text-xs font-bold text-[#00F0FF] mb-1">Nombre comercial de tu tienda</label>
                    <input type="text" name="store_name" value="{{ old('store_name') }}" placeholder="Ej. Panadería Sol o Calzado Urbano" class="w-full px-3.5 py-2 rounded-xl border border-white/15 bg-[#0B0E17] text-white placeholder:text-slate-500 text-sm focus:outline-none focus:border-[#00F0FF]">
                </div>

                <div id="driver-fields" class="hidden bg-[#00F0FF]/10 p-4 rounded-2xl border border-[#00F0FF]/35 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-[#00F0FF] mb-1">Vehículo</label>
                        <select name="vehicle_type" class="w-full px-3 py-2 rounded-xl border border-white/15 bg-[#0B0E17] text-white text-sm focus:outline-none focus:border-[#00F0FF]">
                            <option value="motorcycle">Motocicleta</option>
                            <option value="bicycle">Bicicleta</option>
                            <option value="car">Automóvil</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#00F0FF] mb-1">Placa (opcional)</label>
                        <input type="text" name="license_plate" value="{{ old('license_plate') }}" class="w-full px-3 py-2 rounded-xl border border-white/15 bg-[#0B0E17] text-white text-sm focus:outline-none focus:border-[#00F0FF]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Contraseña</label>
                        <input type="password" name="password" required class="w-full px-4 py-2.5 rounded-xl border border-white/15 bg-[#0B0E17] text-white text-sm focus:outline-none focus:border-[#00F0FF]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Confirmar contraseña</label>
                        <input type="password" name="password_confirmation" required class="w-full px-4 py-2.5 rounded-xl border border-white/15 bg-[#0B0E17] text-white text-sm focus:outline-none focus:border-[#00F0FF]">
                    </div>
                </div>

                <button type="submit" class="w-full py-3 rounded-2xl bg-[#FF3366] hover:bg-[#ff4d7a] text-white text-sm font-bold shadow-lg shadow-[#FF3366]/30 transition cursor-pointer">
                    Crear mi cuenta
                </button>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        (function () {
            const select = document.getElementById('register-account-type');
            const mFields = document.getElementById('merchant-fields');
            const dFields = document.getElementById('driver-fields');
            function toggle() {
                mFields?.classList.toggle('hidden', select.value !== 'merchant_admin');
                dFields?.classList.toggle('hidden', select.value !== 'driver');
            }
            select?.addEventListener('change', toggle);
            toggle();
        })();
    </script>
    @endpush
</x-layouts.mall>
