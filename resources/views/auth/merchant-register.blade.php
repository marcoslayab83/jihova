<x-layouts.mall title="Afiliación Comercial · Registrar Tienda en JIHOVA">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="bg-[#121728]/95 rounded-3xl border border-white/10 p-6 sm:p-8 shadow-2xl space-y-6">
            
            <!-- Encabezado del Formulario -->
            <div class="border-b border-white/10 pb-4">
                <span class="inline-block px-3 py-1 rounded-full bg-[#00F0FF]/15 border border-[#00F0FF]/30 text-[#00F0FF] text-[11px] font-bold uppercase tracking-wider mb-2">
                    Comercios Aliados JIHOVA
                </span>
                <h1 class="font-display text-2xl sm:text-3xl font-black text-white">Registro y Afiliación Comercial</h1>
                <p class="text-xs sm:text-sm text-slate-300 mt-1">
                    Únete a la plataforma omnicanal de Venezuela. Gestiona ventas en mostrador (POS), catálogos online y despachos express con inventario centralizado.
                </p>
            </div>

            @if ($errors->any())
                <div class="p-4 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs space-y-1">
                    <strong class="font-bold block">Por favor corrige los siguientes errores:</strong>
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('merchant.register.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- SECCIÓN 1: DATOS FISCALES DEL COMERCIO (SENIAT / VENEZUELA) -->
                <div class="space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#00F0FF] flex items-center gap-2">
                        <span>🏛️</span>
                        <span>1. Datos Fiscales y Jurídicos del Comercio</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Nombre Comercial de la Tienda *</label>
                            <input type="text" name="business_name" value="{{ old('business_name') }}" required
                                placeholder="Ej: Pastelitos Don Carlos"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-white/15 bg-[#0B0E17] text-white text-xs focus:outline-none focus:border-[#00F0FF]">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Razón Social Jurídica *</label>
                            <input type="text" name="legal_name" value="{{ old('legal_name') }}" required
                                placeholder="Ej: Inversiones Don Carlos, C.A."
                                class="w-full px-3.5 py-2.5 rounded-xl border border-white/15 bg-[#0B0E17] text-white text-xs focus:outline-none focus:border-[#00F0FF]">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">RIF Fiscal (SENIAT) *</label>
                            <input type="text" name="rif" value="{{ old('rif') }}" required
                                placeholder="J-12345678-0 o V-12345678-9"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-white/15 bg-[#0B0E17] text-white font-mono uppercase text-xs focus:outline-none focus:border-[#00F0FF]">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Condición Frente al IVA *</label>
                            <select name="iva_condition" required class="w-full px-3.5 py-2.5 rounded-xl border border-white/15 bg-[#0B0E17] text-white text-xs focus:outline-none focus:border-[#00F0FF]">
                                <option value="ordinario" @selected(old('iva_condition') === 'ordinario')>Contribuyente Ordinario (IVA 16%)</option>
                                <option value="contribuyente_especial" @selected(old('iva_condition') === 'contribuyente_especial')>Contribuyente Especial (Retiene IVA / Aplica IGTF 3%)</option>
                                <option value="formal" @selected(old('iva_condition') === 'formal')>Contribuyente Formal (Actividades Exentas)</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Categoría Comercial *</label>
                            <select name="category_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-white/15 bg-[#0B0E17] text-white text-xs focus:outline-none focus:border-[#00F0FF]">
                                <option value="">Selecciona la categoría de tu negocio...</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" @selected(old('category_id') === $cat->id)>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Dirección Fiscal Registrada *</label>
                            <textarea name="fiscal_address" rows="2" required placeholder="Dirección legal según Registro de Información Fiscal (RIF)"
                                class="w-full px-3.5 py-2 rounded-xl border border-white/15 bg-[#0B0E17] text-white text-xs focus:outline-none focus:border-[#00F0FF]">{{ old('fiscal_address') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 2: SUCURSAL PRINCIPAL Y PUNTOS DE VENTA -->
                <div class="space-y-4 pt-4 border-t border-white/10">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#00F0FF] flex items-center gap-2">
                        <span>📍</span>
                        <span>2. Sucursal Principal de Despacho y Mostrador</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Nombre de la Sede / Sucursal *</label>
                            <input type="text" name="branch_name" value="{{ old('branch_name', 'Sede Principal') }}" required
                                placeholder="Ej: Sede Centro o Tienda Maracay"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-white/15 bg-[#0B0E17] text-white text-xs focus:outline-none focus:border-[#00F0FF]">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Teléfono de la Sede (Opcional)</label>
                            <input type="text" name="branch_phone" value="{{ old('branch_phone') }}"
                                placeholder="+58 412 000 0000"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-white/15 bg-[#0B0E17] text-white font-mono text-xs focus:outline-none focus:border-[#00F0FF]">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Dirección Física de la Sucursal *</label>
                            <input type="text" name="branch_address" value="{{ old('branch_address') }}" required
                                placeholder="Avenida, calle, centro comercial, local o punto de referencia"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-white/15 bg-[#0B0E17] text-white text-xs focus:outline-none focus:border-[#00F0FF]">
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 3: DATOS DEL REPRESENTANTE LEGAL / CUENTA DE ACCESO -->
                <div class="space-y-4 pt-4 border-t border-white/10">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#00F0FF] flex items-center gap-2">
                        <span>👤</span>
                        <span>3. Cuenta de Acceso del Representante (Owner)</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Nombre Completo del Representante *</label>
                            <input type="text" name="full_name" value="{{ old('full_name') }}" required
                                placeholder="Ej: Carlos Silva"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-white/15 bg-[#0B0E17] text-white text-xs focus:outline-none focus:border-[#00F0FF]">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Teléfono Personal / WhatsApp *</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" required
                                placeholder="+58 414 123 4567"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-white/15 bg-[#0B0E17] text-white font-mono text-xs focus:outline-none focus:border-[#00F0FF]">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Correo Electrónico de Acceso *</label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                placeholder="gerencia@tutienda.com"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-white/15 bg-[#0B0E17] text-white text-xs focus:outline-none focus:border-[#00F0FF]">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Contraseña (Mínimo 8 caracteres) *</label>
                            <input type="password" name="password" required
                                class="w-full px-3.5 py-2.5 rounded-xl border border-white/15 bg-[#0B0E17] text-white text-xs focus:outline-none focus:border-[#00F0FF]">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Confirmar Contraseña *</label>
                            <input type="password" name="password_confirmation" required
                                class="w-full px-3.5 py-2.5 rounded-xl border border-white/15 bg-[#0B0E17] text-white text-xs focus:outline-none focus:border-[#00F0FF]">
                        </div>
                    </div>
                </div>

                <div class="p-3.5 rounded-2xl bg-amber-500/10 border border-amber-400/20 text-amber-300 text-xs">
                    ℹ️ <strong>Nota de Validación:</strong> Al enviar el formulario, tu tienda se registrará en estado <em>Pendiente de Aprobación</em>. Podrás ingresar de inmediato para configurar tu inventario y Punto de Venta (POS). Una vez verificados tus datos por el Administrador de JIHOVA, tu vitrina pública será visible en el centro comercial.
                </div>

                <button type="submit" class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-[#F43F5E] to-[#6C28FE] text-white font-display font-black text-sm tracking-wide shadow-lg shadow-[#F43F5E]/25 hover:opacity-95 transition cursor-pointer">
                    Enviar Solicitud Comercial y Crear Tienda 🚀
                </button>
            </form>

            <div class="text-center pt-2 border-t border-white/10 text-xs text-slate-400">
                ¿Ya tienes una cuenta comercial o de usuario?
                <a href="{{ route('login') }}" class="text-[#00F0FF] font-bold hover:underline ml-1">Iniciar sesión</a>
            </div>
        </div>
    </div>
</x-layouts.mall>
