<x-layouts.mall title="Suite del Comerciante · JIHOVA">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <!-- Encabezado Suite del Comerciante -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-white/10">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#00F0FF]/15 border border-[#00F0FF]/30 text-xs font-bold text-[#00F0FF] mb-2">
                    <span class="w-2 h-2 rounded-full bg-[#00F0FF] animate-pulse"></span>
                    SUITE PROFESIONAL PARA COMERCIANTES JIHOVA
                </div>
                <h1 class="font-display text-2xl sm:text-3xl font-extrabold text-white">
                    Gestión Integral: Inventario, IA, Publicidad y Ventas
                </h1>
                <p class="text-sm text-slate-400 mt-1">
                    Controla existencias con fotos, realza imágenes con IA, publica anuncios en el Mall y registra tus ventas en un solo lugar.
                </p>
            </div>

            <!-- Accesos rápidos a tiendas -->
            <div class="flex items-center gap-2">
                @foreach ($stores as $s)
                    <a href="{{ route('mall.stores.show', $s) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#121728] hover:bg-[#1a223a] border border-white/15 text-xs font-bold text-slate-200 transition">
                        <span>🏪</span>
                        <span>{{ $s->business_name }}</span>
                        <span class="text-[10px] text-[#00F0FF]">↗</span>
                    </a>
                @endforeach
            </div>
        </div>

        @if (session('status'))
            <div role="status" class="rounded-2xl border border-emerald-400/40 bg-emerald-500/15 px-4 py-3 text-sm font-semibold text-emerald-200 shadow-[0_0_20px_-5px_rgba(52,211,153,0.3)] flex items-center gap-2">
                <span class="text-emerald-400">✓</span>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div role="alert" class="rounded-2xl border border-[#FF3366]/40 bg-[#FF3366]/15 px-4 py-3 text-sm text-rose-100 shadow-[0_0_20px_-5px_rgba(255,51,102,0.3)]">
                <p class="font-bold flex items-center gap-2">
                    <span class="text-[#FF3366]">✕</span>
                    Revisa los datos del formulario:
                </p>
                <ul class="mt-1 list-inside list-disc text-xs text-rose-200">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- KPIs Principales -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-[#121728] rounded-2xl border border-white/10 p-4 shadow-lg">
                <p class="text-xs font-semibold text-slate-400">Por preparar (Online)</p>
                <p class="font-display text-2xl font-extrabold text-amber-300 mt-1">{{ $metrics['pending_count'] }}</p>
            </div>
            <div class="bg-[#121728] rounded-2xl border border-white/10 p-4 shadow-lg">
                <p class="text-xs font-semibold text-slate-400">Listos p/ repartidor</p>
                <p class="font-display text-2xl font-extrabold text-[#6C28FE] mt-1">{{ $metrics['ready_count'] }}</p>
            </div>
            <div class="bg-[#121728] rounded-2xl border border-white/10 p-4 shadow-lg">
                <p class="text-xs font-semibold text-slate-400">Entregados hoy</p>
                <p class="font-display text-2xl font-extrabold text-emerald-300 mt-1">{{ $metrics['delivered_count'] }}</p>
            </div>
            <div class="bg-[#121728] rounded-2xl border border-white/10 p-4 shadow-lg">
                <p class="text-xs font-semibold text-slate-400">Ventas brutas acumuladas</p>
                <p class="font-display text-2xl font-extrabold text-[#00F0FF] mt-1">${{ number_format($metrics['gross_sales'], 2) }}</p>
            </div>
        </div>

        <!-- Pestañas de Navegación Rápida de la Suite -->
        <div class="flex flex-wrap items-center gap-2 border-b border-white/10 pb-3" data-suite-tabs>
            <a href="#inventario" class="px-4 py-2 rounded-xl bg-[#6C28FE] text-white text-xs font-bold shadow-md shadow-[#6C28FE]/30 hover:opacity-95 transition flex items-center gap-1.5">
                <span>📦</span>
                <span>Inventario & Catálogo con Fotos</span>
            </a>
            <a href="#publicidad" class="px-4 py-2 rounded-xl bg-[#121728] text-slate-300 hover:text-white border border-white/10 hover:border-[#00F0FF]/40 text-xs font-bold transition flex items-center gap-1.5">
                <span>🚀</span>
                <span>Publicidad en Home JIHOVA ({{ $adBanners->count() }})</span>
            </a>
            <a href="#pos" class="px-4 py-2 rounded-xl bg-[#121728] text-slate-300 hover:text-white border border-white/10 hover:border-[#00F0FF]/40 text-xs font-bold transition flex items-center gap-1.5">
                <span>🛒</span>
                <span>Punto de Venta (POS) & Mostrador</span>
            </a>
            <a href="#pedidos-online" class="px-4 py-2 rounded-xl bg-[#121728] text-slate-300 hover:text-white border border-white/10 hover:border-[#00F0FF]/40 text-xs font-bold transition flex items-center gap-1.5">
                <span>🛵</span>
                <span>Pedidos Online ({{ $activeOrders->count() }})</span>
            </a>
            <a href="#finanzas" class="px-4 py-2 rounded-xl bg-[#121728] text-slate-300 hover:text-white border border-white/10 hover:border-[#00F0FF]/40 text-xs font-bold transition flex items-center gap-1.5">
                <span>📊</span>
                <span>Finanzas & Rentabilidad</span>
            </a>
        </div>

        <!-- SECCIÓN 1: INVENTARIO CON FOTOS Y ESTUDIO IA -->
        <section id="inventario" class="space-y-6 pt-4">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[#00F0FF]">
                        <span>Pilar 1 & 2</span>
                        <span>·</span>
                        <span>Catálogo Digital</span>
                    </div>
                    <h2 class="font-display text-2xl font-extrabold text-white">📦 Inventario con Fotos y Estudio IA</h2>
                    <p class="text-xs text-slate-400">Sube tus productos con fotografías reales, o utiliza el motor de IA para realzar su iluminación estilo Cyberpunk Neón.</p>
                </div>
            </div>

            <!-- Formulario de Publicación con Carga de Foto y Realce IA -->
            @foreach ($stores as $store)
                <div class="bg-[#121728] rounded-3xl border border-white/10 p-6 shadow-xl space-y-6">
                    <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-white/10">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-display text-xl font-extrabold text-white">{{ $store->business_name }}</h3>
                                <x-status-badge :status="$store->status" />
                            </div>
                            <p class="text-xs text-slate-400">{{ $store->tagline }}</p>
                        </div>
                        <span class="text-xs text-slate-400 font-medium">Categoría: <strong class="text-slate-200">{{ $store->category->name }}</strong></span>
                    </div>

                    <!-- Publicar Nuevo Artículo -->
                    <form action="{{ route('merchant.products.store') }}" method="POST" enctype="multipart/form-data" class="p-5 rounded-2xl border border-dashed border-[#00F0FF]/40 bg-[#0B0E17]/65 space-y-4">
                        @csrf
                        <input type="hidden" name="store_id" value="{{ $store->id }}">
                        
                        <div class="flex items-center justify-between gap-2">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-[#00F0FF] flex items-center gap-1.5">
                                <span>+</span>
                                <span>Publicar nuevo producto con foto y opciones de IA</span>
                            </h4>
                            <span class="text-[11px] text-slate-400">Formatos: JPG, PNG, WebP (máx 5MB)</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-300 mb-1">Nombre del artículo *</label>
                                <input type="text" name="name" placeholder="Ej: Zapatillas Urban Neon" required class="w-full px-3 py-2 rounded-xl border border-white/15 bg-[#121728] text-white text-xs placeholder:text-slate-500 focus:outline-none focus:border-[#00F0FF]">
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-300 mb-1">Sucursal inicial *</label>
                                <select name="branch_id" required class="w-full px-3 py-2 rounded-xl border border-white/15 bg-[#121728] text-white text-xs focus:outline-none focus:border-[#00F0FF]">
                                    @foreach ($store->branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->branch_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-300 mb-1">Categoría del Mall *</label>
                                <select name="category_id" required class="w-full px-3 py-2 rounded-xl border border-white/15 bg-[#121728] text-white text-xs focus:outline-none focus:border-[#00F0FF]">
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-300 mb-1">Stock inicial *</label>
                                <input type="number" name="stock_quantity" value="15" min="1" max="9999" required class="w-full px-3 py-2 rounded-xl border border-white/15 bg-[#121728] text-white text-xs focus:outline-none focus:border-[#00F0FF]">
                            </div>
                        </div>

                        <!-- Precios y Márgenes -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-300 mb-1">Costo ($ USD) *</label>
                                <input type="number" min="0.50" max="99999" step="0.01" name="cost_price" placeholder="Ej: 10.00" required class="w-full px-3 py-2 rounded-xl border border-white/15 bg-[#121728] text-white text-xs focus:outline-none focus:border-[#00F0FF]">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-300 mb-1">Margen de ganancia % *</label>
                                <input type="number" min="0" max="1000" step="0.01" name="profit_margin_percent" value="35" required class="w-full px-3 py-2 rounded-xl border border-white/15 bg-[#121728] text-white text-xs focus:outline-none focus:border-[#00F0FF]">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-300 mb-1">Descuento % (opcional)</label>
                                <input type="number" min="0" max="99" step="0.01" name="discount_percent" value="0" class="w-full px-3 py-2 rounded-xl border border-white/15 bg-[#121728] text-white text-xs focus:outline-none focus:border-[#00F0FF]">
                            </div>
                        </div>

                        <!-- Carga de Fotografía y Motor de IA con Soporte de Cámara de Teléfono -->
                        <div class="p-4 rounded-2xl bg-[#0B0E17]/80 border border-white/10 space-y-4" data-photo-uploader-box>
                            <div class="flex flex-wrap items-center justify-between gap-2 pb-2 border-b border-white/10">
                                <label class="text-xs font-bold text-slate-200 flex items-center gap-2">
                                    <span class="text-[#00F0FF] text-sm">📸</span>
                                    <span>Fotografía del Artículo</span>
                                </label>
                                <span class="text-[11px] text-slate-400">Puedes tomar la foto en el momento con tu teléfono o subir un archivo</span>
                            </div>

                            <!-- Inputs Ocultos de Archivos y Cámara -->
                            <input type="file" name="image_camera" accept="image/*" capture="environment" class="hidden" data-camera-input>
                            <input type="file" name="image_file" accept="image/*" class="hidden" data-file-input>
                            <input type="hidden" name="image_base64" data-base64-input>

                            <!-- Opciones de Captura: Cámara vs Galería vs Visor en Vivo -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3" data-upload-options>
                                <!-- Opción 1: Cámara Nativa de Smartphone -->
                                <button type="button" data-trigger-camera class="group relative flex flex-col items-center justify-center p-4 rounded-2xl border border-dashed border-[#00F0FF]/50 bg-[#00F0FF]/5 hover:bg-[#00F0FF]/15 transition cursor-pointer text-center">
                                    <div class="w-11 h-11 rounded-2xl bg-[#00F0FF]/20 border border-[#00F0FF]/40 flex items-center justify-center text-xl text-[#00F0FF] group-hover:scale-110 transition shadow-[0_0_15px_rgba(0,240,255,0.3)]">
                                        📱
                                    </div>
                                    <span class="mt-2 text-xs font-extrabold text-white group-hover:text-[#00F0FF] transition">Tomar Foto con Cámara</span>
                                    <span class="text-[10px] text-slate-400 mt-0.5">Abre la cámara trasera de tu teléfono</span>
                                </button>

                                <!-- Opción 2: Subir desde Galería o PC -->
                                <button type="button" data-trigger-file class="group relative flex flex-col items-center justify-center p-4 rounded-2xl border border-white/15 bg-[#121728] hover:border-[#6C28FE]/60 hover:bg-[#6C28FE]/10 transition cursor-pointer text-center">
                                    <div class="w-11 h-11 rounded-2xl bg-[#6C28FE]/20 border border-[#6C28FE]/40 flex items-center justify-center text-xl text-[#6C28FE] group-hover:scale-110 transition shadow-[0_0_15px_rgba(108,40,254,0.3)]">
                                        📁
                                    </div>
                                    <span class="mt-2 text-xs font-bold text-white group-hover:text-purple-300 transition">Elegir de Galería / PC</span>
                                    <span class="text-[10px] text-slate-400 mt-0.5">Formatos JPG, PNG, WebP</span>
                                </button>

                                <!-- Opción 3: Visor de Cámara en Vivo con Retícula Neón -->
                                <button type="button" data-trigger-live-view class="group relative flex flex-col items-center justify-center p-4 rounded-2xl border border-white/15 bg-[#121728] hover:border-[#FF3366]/60 hover:bg-[#FF3366]/10 transition cursor-pointer text-center">
                                    <div class="w-11 h-11 rounded-2xl bg-[#FF3366]/20 border border-[#FF3366]/40 flex items-center justify-center text-xl text-[#FF3366] group-hover:scale-110 transition shadow-[0_0_15px_rgba(255,51,102,0.3)]">
                                        ⚡
                                    </div>
                                    <span class="mt-2 text-xs font-bold text-white group-hover:text-rose-300 transition">Visor de Cámara en Vivo</span>
                                    <span class="text-[10px] text-slate-400 mt-0.5">Encuadre y escáner en pantalla</span>
                                </button>
                            </div>

                            <!-- Previsualización Instantánea de la Foto Capturada -->
                            <div data-photo-preview class="hidden p-3.5 rounded-2xl bg-[#121728] border border-[#00F0FF]/40 shadow-lg">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-3.5 min-w-0">
                                        <div class="relative w-18 h-18 rounded-xl overflow-hidden bg-[#0B0E17] border border-white/20 shrink-0">
                                            <img data-photo-preview-img src="" alt="Vista previa" class="w-full h-full object-cover">
                                            <span class="absolute bottom-1 right-1 px-1.5 py-0.5 rounded bg-[#0B0E17]/85 text-[9px] font-bold text-[#00F0FF] border border-[#00F0FF]/40">
                                                LISTA
                                            </span>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-extrabold text-white flex items-center gap-1.5">
                                                <span class="text-emerald-400">✓</span>
                                                <span data-photo-filename class="truncate">Foto lista para publicar</span>
                                            </p>
                                            <p class="text-[11px] text-slate-400 mt-0.5" data-photo-meta>
                                                Lista para procesar con IA y publicar en escaparate.
                                            </p>
                                        </div>
                                    </div>
                                    <button type="button" data-clear-photo class="px-3 py-1.5 rounded-xl bg-rose-500/15 border border-rose-400/30 text-rose-300 hover:bg-rose-500/25 text-xs font-bold transition shrink-0 cursor-pointer">
                                        ✕ Tomar otra
                                    </button>
                                </div>
                            </div>

                            <!-- Opción alternativa por URL -->
                            <div class="pt-1">
                                <details class="group text-xs">
                                    <summary class="text-slate-400 hover:text-slate-200 cursor-pointer font-medium select-none">
                                        + O ingresar enlace (URL) de imagen externa
                                    </summary>
                                    <div class="pt-2">
                                        <input type="url" name="image_url" placeholder="https://ejemplo.com/foto-producto.jpg" data-url-input class="w-full px-3 py-2 rounded-xl border border-white/15 bg-[#121728] text-white text-xs placeholder:text-slate-500 focus:outline-none focus:border-[#00F0FF]">
                                    </div>
                                </details>
                            </div>

                            <!-- Módulo de Estudio Neón IA -->
                            <div class="pt-3 border-t border-white/10 flex flex-wrap items-center justify-between gap-3">
                                <label class="flex items-center gap-2.5 cursor-pointer">
                                    <input type="checkbox" name="auto_enhance_ai" value="1" checked class="w-4 h-4 rounded border-white/20 bg-[#0B0E17] accent-[#00F0FF]">
                                    <span class="text-xs font-bold text-white flex items-center gap-1.5">
                                        <span class="text-[#00F0FF]">✨ Realzar con Estudio Neón IA</span>
                                        <span class="text-[10px] text-slate-400 font-normal">(Aplica pedestal holográfico y halo Cyber Cyan / Hyper-Violet)</span>
                                    </span>
                                </label>

                                <div class="flex items-center gap-2">
                                    <span class="text-[11px] text-slate-400">Preset:</span>
                                    <select name="ai_preset" class="px-2.5 py-1 rounded-lg border border-white/15 bg-[#0B0E17] text-white text-xs">
                                        @foreach ($aiPresets as $key => $preset)
                                            <option value="{{ $key }}">{{ $preset['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-300 mb-1">Descripción del producto *</label>
                            <input type="text" name="description" placeholder="Descripción breve y atractiva para los compradores del Mall..." required class="w-full px-3 py-2 rounded-xl border border-white/15 bg-[#121728] text-white text-xs placeholder:text-slate-500 focus:outline-none focus:border-[#00F0FF]">
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#6C28FE] to-[#FF3366] text-white text-xs font-extrabold shadow-lg shadow-[#FF3366]/30 hover:opacity-95 transition cursor-pointer flex items-center gap-2">
                                <span>✓ Publicar en Tienda y Catálogo</span>
                            </button>
                        </div>
                    </form>

                    <!-- Lista de Productos y Catálogo de la Tienda -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-extrabold text-white">Catálogo de Productos de {{ $store->business_name }} ({{ $store->products->count() }})</h4>
                            <span class="text-xs text-slate-400">Fotografías reales y estado en escaparate</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @forelse ($store->products as $product)
                                <article class="rounded-2xl border border-white/10 bg-[#0B0E17]/75 p-4 flex flex-col justify-between space-y-3 relative group hover:border-[#00F0FF]/40 transition">
                                    <!-- Miniatura y Badges -->
                                    <div class="relative aspect-[4/3] rounded-xl overflow-hidden bg-[#121728] border border-white/10">
                                        <img
                                            src="{{ $product->ai_enhanced_image_url ?: ($product->image_url ?: '/images/catalog/hero-mall.jpg') }}"
                                            alt="{{ $product->name }}"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                        >
                                        @if ($product->ai_enhanced_image_url)
                                            <span class="absolute top-2 left-2 px-2.5 py-0.5 rounded-full bg-[#0B0E17]/85 backdrop-blur border border-[#00F0FF]/50 text-[10px] font-extrabold text-[#00F0FF] flex items-center gap-1 shadow-sm">
                                                <span>✨</span>
                                                <span>IA HD</span>
                                            </span>
                                        @endif
                                        <span class="absolute top-2 right-2 px-2 py-0.5 rounded-full bg-[#FF3366] text-[10px] font-extrabold text-white shadow-sm">
                                            ${{ number_format((float) $product->base_price, 2) }}
                                        </span>
                                    </div>

                                    <div>
                                        <h5 class="font-display font-bold text-sm text-white line-clamp-1">{{ $product->name }}</h5>
                                        <p class="text-[11px] text-slate-400 line-clamp-2 mt-0.5">{{ $product->description }}</p>
                                        
                                        <div class="mt-2 pt-2 border-t border-white/10 flex items-center justify-between text-[11px]">
                                            <span class="text-slate-400">Costo: <strong class="text-slate-200">${{ number_format((float) $product->cost_price, 2) }}</strong></span>
                                            <span class="text-emerald-400 font-semibold">+{{ $product->profit_margin_percent }}% margen</span>
                                        </div>
                                    </div>

                                    <!-- Acciones del Producto: Estudio IA, Anuncio y Precios -->
                                    <div class="space-y-2 pt-2 border-t border-white/10">
                                        <!-- Formulario Estudio IA -->
                                        <form action="{{ route('merchant.products.ai-enhance', $product) }}" method="POST" class="flex items-center gap-1.5">
                                            @csrf
                                            <select name="preset" class="flex-1 px-2 py-1.5 rounded-lg border border-white/15 bg-[#121728] text-white text-[11px]">
                                                @foreach ($aiPresets as $key => $preset)
                                                    <option value="{{ $key }}">{{ $preset['name'] }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-[#6C28FE] hover:bg-[#7d42ff] text-white text-[11px] font-bold transition flex items-center gap-1 shrink-0">
                                                <span>✨</span>
                                                <span>Realzar</span>
                                            </button>
                                        </form>

                                        <!-- Convertir en Banner Publicitario -->
                                        <form action="{{ route('merchant.ai.generate-banner') }}" method="POST" class="flex items-center gap-1.5">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                            <input type="hidden" name="placement" value="hero_carousel">
                                            <button type="submit" class="w-full px-3 py-1.5 rounded-lg border border-[#00F0FF]/40 bg-[#00F0FF]/10 hover:bg-[#00F0FF]/20 text-[#00F0FF] text-[11px] font-extrabold transition flex items-center justify-center gap-1.5">
                                                <span>🚀</span>
                                                <span>Generar Banner 16:9 con IA</span>
                                            </button>
                                        </form>

                                        <!-- Modificar Precio Rápido o Eliminar -->
                                        <div class="flex items-center justify-between gap-2 pt-1 text-[11px]">
                                            <form action="{{ route('merchant.products.pricing', $product) }}" method="POST" class="flex items-center gap-1.5 flex-1">
                                                @csrf
                                                @method('PATCH')
                                                <input type="number" step="0.01" name="cost_price" value="{{ $product->cost_price }}" title="Costo" class="w-16 px-1.5 py-1 rounded bg-[#121728] border border-white/15 text-white text-[11px]">
                                                <input type="number" step="0.01" name="profit_margin_percent" value="{{ $product->profit_margin_percent }}" title="Margen %" class="w-14 px-1.5 py-1 rounded bg-[#121728] border border-white/15 text-white text-[11px]">
                                                <input type="hidden" name="discount_percent" value="{{ $product->discount_percent }}">
                                                <button type="submit" class="px-2 py-1 rounded bg-white/10 hover:bg-white/20 text-slate-200 font-bold">
                                                    Guardar
                                                </button>
                                            </form>

                                            <form action="{{ route('merchant.products.destroy', $product) }}" method="POST" onsubmit="return confirm('¿Eliminar este producto del catálogo?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 text-slate-500 hover:text-rose-400 transition" title="Eliminar producto">
                                                    🗑
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </article>
                            @empty
                                <div class="col-span-full py-8 text-center text-xs text-slate-400 bg-[#0B0E17]/40 rounded-2xl border border-white/5">
                                    Aún no has registrado productos en esta tienda. Usa el formulario superior para publicar tu primer artículo con fotos.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach
        </section>

        <!-- SECCIÓN 2: PUBLICIDAD & VITRINAS EN HOME JIHOVA -->
        <section id="publicidad" class="space-y-6 pt-6 border-t border-white/10">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[#FF3366]">
                        <span>Pilar 3</span>
                        <span>·</span>
                        <span>Alcance y Tráfico</span>
                    </div>
                    <h2 class="font-display text-2xl font-extrabold text-white">🚀 Publicidad & Vitrinas en la Página Principal</h2>
                    <p class="text-xs text-slate-400">Tus anuncios en el Carrusel Principal (Hero) y en las Vitrinas Patrocinadas de JIHOVA con medición de clics en vivo.</p>
                </div>
            </div>

            <!-- Creador de Anuncio / Banner -->
            <div class="bg-[#121728] rounded-3xl border border-white/10 p-6 shadow-xl space-y-6">
                <form action="{{ route('merchant.ads.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-white/10">
                        <h3 class="font-display text-lg font-extrabold text-white flex items-center gap-2">
                            <span>📢</span>
                            <span>Crear Nuevo Anuncio Publicitario</span>
                        </h3>
                        <span class="text-xs text-[#00F0FF]">Impacta a miles de compradores activos</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Tienda emisora *</label>
                            <select name="store_id" required class="w-full px-3 py-2 rounded-xl border border-white/15 bg-[#0B0E17] text-white text-xs">
                                @foreach ($stores as $st)
                                    <option value="{{ $st->id }}">{{ $st->business_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Producto promocionado (opcional)</label>
                            <select name="product_id" class="w-full px-3 py-2 rounded-xl border border-white/15 bg-[#0B0E17] text-white text-xs">
                                <option value="">-- Toda la tienda (sin producto específico) --</option>
                                @foreach ($stores as $st)
                                    @foreach ($st->products as $p)
                                        <option value="{{ $p->id }}">{{ $st->business_name }} · {{ $p->name }}</option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Ubicación en JIHOVA *</label>
                            <select name="placement" required class="w-full px-3 py-2 rounded-xl border border-white/15 bg-[#0B0E17] text-white text-xs">
                                <option value="hero_carousel">Carrusel Principal (Hero 16:9)</option>
                                <option value="vitrina_grid">Vitrina Patrocinada (Sección Comercial)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Título llamativo del anuncio *</label>
                            <input type="text" name="title" maxlength="150" placeholder="Ej: Nueva Colección Deportiva 2026" required class="w-full px-3 py-2 rounded-xl border border-white/15 bg-[#0B0E17] text-white text-xs placeholder:text-slate-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Subtítulo o gancho</label>
                            <input type="text" name="subtitle" maxlength="255" placeholder="Ej: Hasta 30% OFF en envíos coordinados" class="w-full px-3 py-2 rounded-xl border border-white/15 bg-[#0B0E17] text-white text-xs placeholder:text-slate-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Texto del botón CTA</label>
                            <input type="text" name="cta_text" maxlength="50" value="Comprar Ahora" class="w-full px-3 py-2 rounded-xl border border-white/15 bg-[#0B0E17] text-white text-xs">
                        </div>
                    </div>

                    <!-- Imagen del Banner -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 rounded-2xl bg-[#0B0E17]/65 border border-white/10">
                        <div>
                            <label class="block text-xs font-bold text-slate-200 mb-1">Subir imagen 16:9 (o banner diseñado)</label>
                            <input type="file" name="banner_file" accept="image/*" class="w-full text-xs text-slate-300 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#FF3366] file:text-white hover:file:bg-[#ff4d7a] cursor-pointer">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">O usar URL directa de banner</label>
                            <input type="url" name="image_url" placeholder="https://ejemplo.com/banner-16-9.jpg" class="w-full px-3 py-2 rounded-xl border border-white/15 bg-[#121728] text-white text-xs placeholder:text-slate-500">
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#00F0FF] hover:bg-cyan-300 text-[#0B0E17] text-xs font-extrabold shadow-lg shadow-[#00F0FF]/30 transition cursor-pointer">
                            🚀 Publicar Anuncio en JIHOVA
                        </button>
                    </div>
                </form>

                <!-- Listado de Banners Actuales -->
                <div class="space-y-4 pt-4 border-t border-white/10">
                    <h4 class="text-sm font-extrabold text-white">Tus Anuncios Activos y Campañas ({{ $adBanners->count() }})</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        @forelse ($adBanners as $ad)
                            <article class="rounded-2xl border border-white/10 bg-[#0B0E17]/75 overflow-hidden flex flex-col justify-between space-y-3">
                                <div class="relative aspect-[16/9] w-full bg-[#121728]">
                                    <img src="{{ $ad->image_url }}" alt="{{ $ad->title }}" class="w-full h-full object-cover">
                                    <span class="absolute top-2 left-2 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold {{ $ad->is_active ? 'bg-emerald-500/90 text-white' : 'bg-slate-700 text-slate-300' }}">
                                        {{ $ad->is_active ? '● Activo en Home' : '○ Pausado' }}
                                    </span>
                                    <span class="absolute top-2 right-2 px-2 py-0.5 rounded-full bg-[#121728]/90 border border-white/20 text-[10px] font-bold text-[#00F0FF]">
                                        👁 {{ $ad->clicks_count }} clics
                                    </span>
                                </div>

                                <div class="p-4 space-y-2">
                                    <div class="flex items-center justify-between text-[11px] text-slate-400">
                                        <span>{{ $ad->store->business_name }}</span>
                                        <span class="text-[#6C28FE] font-bold">{{ $ad->placement === 'hero_carousel' ? 'Carrusel Principal' : 'Vitrina Patrocinada' }}</span>
                                    </div>
                                    <h5 class="font-display font-bold text-sm text-white line-clamp-1">{{ $ad->title }}</h5>
                                    @if ($ad->subtitle)
                                        <p class="text-xs text-slate-300 line-clamp-2">{{ $ad->subtitle }}</p>
                                    @endif

                                    <div class="pt-3 border-t border-white/10 flex items-center justify-between gap-2">
                                        <form action="{{ route('merchant.ads.toggle', $ad) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-bold border transition {{ $ad->is_active ? 'bg-amber-500/15 border-amber-400/30 text-amber-300 hover:bg-amber-500/25' : 'bg-emerald-500/15 border-emerald-400/30 text-emerald-300 hover:bg-emerald-500/25' }}">
                                                {{ $ad->is_active ? 'Pausar' : 'Activar' }}
                                            </button>
                                        </form>

                                        <form action="{{ route('merchant.ads.destroy', $ad) }}" method="POST" onsubmit="return confirm('¿Eliminar este anuncio?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-500/15 border border-rose-400/30 text-rose-300 hover:bg-rose-500/25 text-xs font-bold transition">
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="col-span-full py-8 text-center text-xs text-slate-400 bg-[#0B0E17]/40 rounded-2xl border border-white/5">
                                No tienes anuncios activos. ¡Publica tu primer banner o genera uno con IA desde el catálogo!
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        <!-- SECCIÓN 3: PUNTO DE VENTA (POS) Y REGISTRO DE VENTAS FÍSICAS -->
        <section id="pos" class="space-y-6 pt-6 border-t border-white/10">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-emerald-400">
                        <span>Pilar 4</span>
                        <span>·</span>
                        <span>Caja y Facturación</span>
                    </div>
                    <h2 class="font-display text-2xl font-extrabold text-white">🛒 Punto de Venta (POS) & Registro de Ventas</h2>
                    <p class="text-xs text-slate-400">Registra tus ventas de mostrador, WhatsApp o pedidos telefónicos. Las existencias se descuentan automáticamente del inventario.</p>
                </div>
            </div>

            <!-- Terminal POS por Sucursal -->
            @foreach ($stores as $store)
                @foreach ($store->branches as $branch)
                    <div class="bg-[#121728] rounded-3xl border border-white/10 p-6 shadow-xl space-y-5">
                        <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-white/10">
                            <div>
                                <h3 class="font-display text-lg font-bold text-white">{{ $branch->branch_name }}</h3>
                                <p class="text-xs text-slate-400">{{ $store->business_name }} · {{ $branch->address }}</p>
                            </div>
                            <form action="{{ route('merchant.branches.toggle', $branch) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3.5 py-1.5 rounded-xl text-xs font-bold cursor-pointer border transition {{ $branch->is_open ? 'bg-emerald-500/15 border-emerald-400/40 text-emerald-300 hover:bg-emerald-500/25' : 'bg-white/10 border-white/15 text-slate-300 hover:bg-white/20' }}">
                                    {{ $branch->is_open ? '● Sucursal Abierta (Cerrar)' : '○ Sucursal Cerrada (Abrir)' }}
                                </button>
                            </form>
                        </div>

                        <!-- Formulario Dinámico de Registro de Venta -->
                        <form action="{{ route('merchant.sales.store') }}" method="POST" class="space-y-4 rounded-2xl border border-[#6C28FE]/40 bg-[#0B0E17]/80 p-5 shadow-inner" data-sale-form data-next-item-index="1">
                            @csrf
                            <input type="hidden" name="branch_id" value="{{ $branch->id }}">

                            <div class="flex flex-wrap items-center justify-between gap-2 pb-2 border-b border-white/10">
                                <div>
                                    <h4 class="text-sm font-extrabold text-white flex items-center gap-2">
                                        <span class="text-emerald-400">⚡</span>
                                        <span>Caja Rápida / Nueva Venta</span>
                                    </h4>
                                    <p class="text-[11px] text-slate-400">Selecciona los artículos vendidos para descontar del stock en tiempo real.</p>
                                </div>
                                <button type="button" data-add-sale-item class="rounded-xl border border-[#00F0FF]/40 bg-[#00F0FF]/10 px-3.5 py-1.5 text-xs font-extrabold text-[#00F0FF] hover:bg-[#00F0FF]/20 transition cursor-pointer">
                                    + Agregar línea de artículo
                                </button>
                            </div>

                            <div data-sale-items class="space-y-2.5">
                                <div data-sale-row class="grid gap-2 sm:grid-cols-[1fr_120px_auto] items-center">
                                    <select name="items[0][product_id]" required class="w-full rounded-xl border border-white/15 bg-[#121728] px-3 py-2 text-xs text-white focus:border-[#00F0FF] focus:outline-none">
                                        @foreach ($branch->inventory as $inv)
                                            @if ($inv->is_available && $inv->stock_quantity > 0)
                                                <option value="{{ $inv->product_id }}">{{ $inv->product->name }} · ${{ number_format((float) $inv->price, 2) }} ({{ $inv->stock_quantity }} disponibles)</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <input type="number" name="items[0][quantity]" min="1" max="9999" value="1" required aria-label="Cantidad" class="rounded-xl border border-white/15 bg-[#121728] px-3 py-2 text-xs text-white text-center focus:border-[#00F0FF] focus:outline-none">
                                    <button type="button" data-remove-sale-item class="rounded-xl px-3 py-2 text-xs font-bold text-rose-400 hover:bg-rose-500/10 transition cursor-pointer">✕</button>
                                </div>
                            </div>

                            <template data-sale-row-template>
                                <div data-sale-row class="grid gap-2 sm:grid-cols-[1fr_120px_auto] items-center">
                                    <select name="items[__INDEX__][product_id]" required class="w-full rounded-xl border border-white/15 bg-[#121728] px-3 py-2 text-xs text-white focus:border-[#00F0FF] focus:outline-none">
                                        @foreach ($branch->inventory as $inv)
                                            @if ($inv->is_available && $inv->stock_quantity > 0)
                                                <option value="{{ $inv->product_id }}">{{ $inv->product->name }} · ${{ number_format((float) $inv->price, 2) }} ({{ $inv->stock_quantity }} disponibles)</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <input type="number" name="items[__INDEX__][quantity]" min="1" max="9999" value="1" required aria-label="Cantidad" class="rounded-xl border border-white/15 bg-[#121728] px-3 py-2 text-xs text-white text-center focus:border-[#00F0FF] focus:outline-none">
                                    <button type="button" data-remove-sale-item class="rounded-xl px-3 py-2 text-xs font-bold text-rose-400 hover:bg-rose-500/10 transition cursor-pointer">✕</button>
                                </div>
                            </template>

                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 pt-2">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-300 mb-1">Canal de venta</label>
                                    <select name="channel" required class="w-full rounded-xl border border-white/15 bg-[#121728] px-3 py-2 text-xs text-white">
                                        <option value="in_store">Mostrador / Tienda física</option>
                                        <option value="whatsapp">WhatsApp / Chat directo</option>
                                        <option value="phone">Llamada telefónica</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-300 mb-1">Forma de pago</label>
                                    <select name="payment_method" required class="w-full rounded-xl border border-white/15 bg-[#121728] px-3 py-2 text-xs text-white">
                                        <option value="cash_usd">Efectivo USD ($)</option>
                                        <option value="pago_movil">Pago Móvil (Bs)</option>
                                        <option value="pos_card">Punto de Venta / Tarjeta</option>
                                        <option value="binance_usdt">USDT Binance</option>
                                        <option value="transfer">Transferencia Bancaria</option>
                                        <option value="cash_ves">Efectivo Bs</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-300 mb-1">Nombre del cliente</label>
                                    <input type="text" name="customer_name" maxlength="150" placeholder="Cliente en mostrador" class="w-full rounded-xl border border-white/15 bg-[#121728] px-3 py-2 text-xs text-white placeholder:text-slate-500">
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-300 mb-1">Referencia o comprobante</label>
                                    <input type="text" name="payment_reference" maxlength="100" placeholder="Ej: Ref #129384" class="w-full rounded-xl border border-white/15 bg-[#121728] px-3 py-2 text-xs text-white placeholder:text-slate-500">
                                </div>
                            </div>

                            <button type="submit" @disabled($branch->inventory->where('is_available', true)->where('stock_quantity', '>', 0)->isEmpty()) class="w-full rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 px-4 py-3 text-xs font-extrabold text-white transition hover:opacity-95 disabled:cursor-not-allowed disabled:opacity-50 shadow-lg shadow-emerald-500/25 cursor-pointer">
                                ✓ Registrar Venta y Descontar Stock
                            </button>
                        </form>

                        <!-- Movimientos Manuales de Inventario (Entradas/Salidas) -->
                        <form action="{{ route('merchant.inventory-movements.store') }}" method="POST" class="grid gap-2.5 rounded-2xl border border-white/10 bg-[#0B0E17]/60 p-4 sm:grid-cols-2 lg:grid-cols-5 items-center">
                            @csrf
                            <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                            <select name="product_id" required class="rounded-xl border border-white/15 bg-[#121728] px-3 py-2 text-xs text-white">
                                @foreach ($branch->inventory as $inv)
                                    <option value="{{ $inv->product_id }}">{{ $inv->product->name }} (stock actual: {{ $inv->stock_quantity }})</option>
                                @endforeach
                            </select>
                            <select name="type" required class="rounded-xl border border-white/15 bg-[#121728] px-3 py-2 text-xs text-white">
                                <option value="entry">Entrada de mercancía / Proveedor</option>
                                <option value="exit">Salida / Merma / Ajuste</option>
                            </select>
                            <input type="number" name="quantity" min="1" max="9999" value="1" required placeholder="Cantidad" class="rounded-xl border border-white/15 bg-[#121728] px-3 py-2 text-xs text-white">
                            <input type="text" name="reason" maxlength="255" required placeholder="Motivo del movimiento..." class="rounded-xl border border-white/15 bg-[#121728] px-3 py-2 text-xs text-white placeholder:text-slate-500">
                            <button type="submit" class="rounded-xl bg-[#00F0FF] hover:bg-cyan-300 px-3 py-2 text-xs font-extrabold text-[#0B0E17] transition">
                                Guardar movimiento
                            </button>
                        </form>
                    </div>
                @endforeach
            @endforeach
        </section>

        <!-- SECCIÓN 4: PEDIDOS ONLINE DEL MALL -->
        <section id="pedidos-online" class="space-y-4 pt-6 border-t border-white/10">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-display text-xl font-extrabold text-white">🛵 Pedidos Online Activos ({{ $activeOrders->count() }})</h2>
                    <p class="text-xs text-slate-400">Flujo de preparación, empaque y despacho para repartidores de JIHOVA.</p>
                </div>
            </div>

            @forelse ($activeOrders as $subOrder)
                <div class="bg-[#121728] rounded-3xl border border-white/10 p-5 shadow-xl space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-white/10">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-white">{{ $subOrder->sub_order_code }}</span>
                                <x-status-badge :status="$subOrder->status" />
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5">
                                {{ $subOrder->branch->store->business_name }} ({{ $subOrder->branch->branch_name }}) · Cliente: <span class="text-slate-200">{{ $subOrder->order->customer->full_name }}</span>
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="text-lg font-extrabold text-[#00F0FF]">${{ number_format((float) $subOrder->subtotal, 2) }}</span>
                            <span class="block text-[11px] font-mono text-slate-400">PIN retiro: <strong class="text-white">{{ $subOrder->pickup_pin }}</strong></span>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="text-sm text-slate-200 space-y-1">
                            @foreach ($subOrder->items as $item)
                                <p><strong>{{ $item->quantity }}x</strong> {{ $item->product_name }} (${{ number_format((float) $item->total_price, 2) }})</p>
                            @endforeach
                            @if ($subOrder->driver)
                                <p class="text-xs font-semibold text-[#00F0FF]">🛵 Repartidor asignado: {{ $subOrder->driver->full_name }}</p>
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-2">
                            @if ($subOrder->status === \App\Enums\SubOrderStatus::Placed)
                                <form action="{{ route('merchant.sub-orders.advance', $subOrder) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="accepted_by_store">
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#6C28FE] hover:bg-[#7d42ff] text-white text-xs font-bold shadow-md shadow-[#6C28FE]/30 cursor-pointer transition">
                                        ✓ Aceptar pedido
                                    </button>
                                </form>
                            @elseif ($subOrder->status === \App\Enums\SubOrderStatus::AcceptedByStore)
                                <form action="{{ route('merchant.sub-orders.advance', $subOrder) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="preparing">
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-[#0B0E17] text-xs font-bold cursor-pointer transition">
                                        🍳 Pasar a Preparando
                                    </button>
                                </form>
                            @elseif ($subOrder->status === \App\Enums\SubOrderStatus::Preparing)
                                <form action="{{ route('merchant.sub-orders.advance', $subOrder) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="ready_for_pickup">
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#00F0FF] hover:bg-cyan-300 text-[#0B0E17] text-xs font-extrabold cursor-pointer transition">
                                        📦 Listo para recoger
                                    </button>
                                </form>
                            @elseif ($subOrder->status === \App\Enums\SubOrderStatus::ReadyForPickup && ! $subOrder->driver_id)
                                <form action="{{ route('merchant.sub-orders.dispatch', $subOrder) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#FF3366] hover:bg-[#ff4d7a] text-white text-xs font-bold shadow-md shadow-[#FF3366]/30 cursor-pointer transition">
                                        🛵 Asignar repartidor cercano
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-[#121728] rounded-3xl border border-white/10 p-8 text-center text-sm text-slate-400">
                    No hay sub-pedidos online pendientes en este momento.
                </div>
            @endforelse
        </section>

        <!-- SECCIÓN 5: FINANZAS, REPORTES Y MOVIMIENTOS -->
        <section id="finanzas" class="space-y-6 pt-6 border-t border-white/10">
            <div>
                <h2 class="font-display text-xl font-extrabold text-white">📊 Finanzas, Rentabilidad e Historial</h2>
                <p class="text-xs text-slate-400">Desglose de utilidad neta y movimientos contables de tus tiendas.</p>
            </div>

            <!-- Reportes de Rentabilidad -->
            <div class="grid gap-4 lg:grid-cols-2">
                @foreach ([
                    ['title' => 'Ganancias de hoy', 'sales' => $todaySales, 'accent' => 'text-[#00F0FF]'],
                    ['title' => 'Ganancias del mes', 'sales' => $monthSales, 'accent' => 'text-emerald-300'],
                ] as $report)
                    <article class="rounded-3xl border border-white/10 bg-[#121728] p-5 shadow-xl">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Reporte de utilidades</p>
                                <h3 class="mt-1 font-display text-xl font-extrabold text-white">{{ $report['title'] }}</h3>
                            </div>
                            <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-slate-300">
                                {{ $report['sales']['sales_count'] }} ventas registradas
                            </span>
                        </div>
                        <div class="mt-5 grid grid-cols-2 gap-3">
                            <div class="rounded-2xl bg-[#0B0E17]/75 p-4">
                                <p class="text-xs text-slate-400">Ventas cobradas</p>
                                <p class="mt-1 text-xl font-extrabold text-white">${{ number_format($report['sales']['gross_sales'], 2) }}</p>
                            </div>
                            <div class="rounded-2xl bg-[#0B0E17]/75 p-4">
                                <p class="text-xs text-slate-400">Utilidad calculada</p>
                                <p class="mt-1 text-xl font-extrabold {{ $report['accent'] }}">${{ number_format($report['sales']['gross_profit'], 2) }}</p>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Historial de Ventas Recientes -->
            <div class="grid gap-5 xl:grid-cols-2">
                <div class="rounded-3xl border border-white/10 bg-[#121728] p-5 shadow-xl">
                    <h3 class="font-display text-lg font-extrabold text-white">Ventas de Mostrador Recientes</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($recentSales as $sale)
                            <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-[#0B0E17]/75 p-3.5 border border-white/5">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="text-xs font-bold text-white">{{ $sale->sale_number }}</p>
                                        <span class="px-2 py-0.5 rounded-full bg-white/10 text-[10px] text-slate-300">{{ $sale->channelLabel() }}</span>
                                    </div>
                                    <p class="mt-1 text-[11px] text-slate-400">{{ $sale->branch->branch_name }} · {{ $sale->sold_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-extrabold text-[#00F0FF]">${{ number_format((float) $sale->total_amount, 2) }}</p>
                                    <a href="{{ route('merchant.sales.show', $sale) }}" class="text-[11px] text-slate-400 hover:text-white underline">Ver recibo</a>
                                </div>
                            </div>
                        @empty
                            <p class="py-6 text-center text-xs text-slate-400">Aún no hay ventas físicas registradas hoy.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-3xl border border-white/10 bg-[#121728] p-5 shadow-xl">
                    <h3 class="font-display text-lg font-extrabold text-white">Ventas Online Entregadas</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($onlineSales as $sale)
                            <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-[#0B0E17]/75 p-3.5 border border-white/5">
                                <div>
                                    <p class="text-xs font-bold text-white">{{ $sale->sub_order_code }} · {{ $sale->order->customer->full_name }}</p>
                                    <p class="mt-1 text-[11px] text-slate-400">{{ $sale->branch->branch_name }} · {{ $sale->delivered_at?->format('d/m/Y H:i') }}</p>
                                </div>
                                <p class="font-extrabold text-emerald-300">${{ number_format((float) $sale->subtotal, 2) }}</p>
                            </div>
                        @empty
                            <p class="py-6 text-center text-xs text-slate-400">No hay pedidos online entregados recientes.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Movimientos de Inventario -->
            <div class="rounded-3xl border border-white/10 bg-[#121728] p-5 shadow-xl">
                <h3 class="font-display text-lg font-extrabold text-white">Auditoría de Movimientos de Stock</h3>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[640px] text-left text-xs">
                        <thead class="border-b border-white/10 text-slate-400">
                            <tr>
                                <th class="py-2 pr-3">Fecha</th>
                                <th class="px-3 py-2">Artículo / Sucursal</th>
                                <th class="px-3 py-2">Movimiento</th>
                                <th class="px-3 py-2">Stock Antes → Después</th>
                                <th class="px-3 py-2">Motivo</th>
                                <th class="py-2 pl-3">Usuario</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 text-slate-300">
                            @forelse ($recentMovements as $movement)
                                <tr>
                                    <td class="py-2.5 pr-3">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-3 py-2.5 font-semibold text-white">
                                        {{ $movement->product->name }}
                                        <span class="block text-[10px] font-normal text-slate-500">{{ $movement->branch->branch_name }}</span>
                                    </td>
                                    <td class="px-3 py-2.5 {{ $movement->type === 'entry' ? 'text-emerald-300' : 'text-rose-300' }}">
                                        {{ $movement->type === 'entry' ? '▲ Entrada' : '▼ Salida' }} · {{ $movement->quantity }}
                                    </td>
                                    <td class="px-3 py-2.5">{{ $movement->stock_before }} → {{ $movement->stock_after }}</td>
                                    <td class="px-3 py-2.5">{{ $movement->reason }}</td>
                                    <td class="py-2.5 pl-3">{{ $movement->user?->full_name ?? 'Sistema' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-6 text-center text-slate-400">Sin movimientos de inventario registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <!-- Modal: Visor de Cámara en Vivo con Escáner Neón -->
    <div id="camera-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#0B0E17]/90 backdrop-blur-xl">
        <div class="relative w-full max-w-lg rounded-3xl border border-[#00F0FF]/50 bg-[#121728] p-5 shadow-[0_0_50px_rgba(0,240,255,0.25)] space-y-4">
            <!-- Header Modal -->
            <div class="flex items-center justify-between pb-3 border-b border-white/10">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-[#00F0FF] animate-pulse"></span>
                    <h3 class="font-display font-extrabold text-white text-sm">Visor de Cámara JIHOVA</h3>
                </div>
                <button type="button" id="close-camera-modal" class="text-slate-400 hover:text-white text-lg p-1 cursor-pointer">✕</button>
            </div>

            <!-- Viewport de Video con Retícula Futurista -->
            <div class="relative aspect-[4/3] rounded-2xl overflow-hidden bg-black border border-white/20 flex items-center justify-center">
                <video id="camera-video" autoplay playsinline class="w-full h-full object-cover"></video>
                <canvas id="camera-canvas" class="hidden"></canvas>

                <!-- Retícula / Guías Neón estilo Cyberpunk -->
                <div class="pointer-events-none absolute inset-4 border border-[#00F0FF]/30 rounded-xl flex items-center justify-center">
                    <!-- Esquinas iluminadas -->
                    <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-[#00F0FF]"></div>
                    <div class="absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-[#00F0FF]"></div>
                    <div class="absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-[#00F0FF]"></div>
                    <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-[#00F0FF]"></div>
                    <!-- Centro de mira -->
                    <div class="w-10 h-10 rounded-full border border-dashed border-[#6C28FE]/60"></div>
                </div>

                <div id="camera-loading" class="absolute inset-0 flex items-center justify-center bg-[#0B0E17]/85 text-xs text-[#00F0FF] font-bold">
                    Conectando con la cámara...
                </div>
            </div>

            <!-- Controles de la Cámara -->
            <div class="flex items-center justify-between gap-3 pt-2">
                <button type="button" id="switch-camera-btn" class="px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-slate-200 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                    <span>🔄</span>
                    <span>Cambiar cámara</span>
                </button>

                <button type="button" id="capture-photo-btn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#00F0FF] to-[#6C28FE] text-white text-xs font-extrabold shadow-lg shadow-[#00F0FF]/30 hover:opacity-95 transition cursor-pointer flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-white animate-ping"></span>
                    <span>🔘 Capturar Foto</span>
                </button>
            </div>
        </div>
    </div>
</x-layouts.mall>

@pushOnce('scripts')
    <script>
        // Gestor de Punto de Venta (POS)
        document.addEventListener('click', function (event) {
            if (!(event.target instanceof Element)) {
                return;
            }

            const addButton = event.target.closest('[data-add-sale-item]');
            if (addButton) {
                const form = addButton.closest('[data-sale-form]');
                const container = form.querySelector('[data-sale-items]');
                const template = form.querySelector('[data-sale-row-template]');
                const index = Number(form.dataset.nextItemIndex);
                const row = template.innerHTML.replace(/__INDEX__/g, String(index));

                container.insertAdjacentHTML('beforeend', row);
                form.dataset.nextItemIndex = String(index + 1);
                return;
            }

            const removeButton = event.target.closest('[data-remove-sale-item]');
            if (removeButton) {
                const container = removeButton.closest('[data-sale-items]');
                if (container.querySelectorAll('[data-sale-row]').length > 1) {
                    removeButton.closest('[data-sale-row]').remove();
                }
                return;
            }
        });

        // Gestor de Fotografía: Cámara del Teléfono, Galería y Visor en Vivo
        (function () {
            let activeBox = null;
            let cameraStream = null;
            let currentFacingMode = 'environment'; // Cámara trasera del teléfono por defecto

            const modal = document.getElementById('camera-modal');
            const video = document.getElementById('camera-video');
            const canvas = document.getElementById('camera-canvas');
            const loading = document.getElementById('camera-loading');
            const closeBtn = document.getElementById('close-camera-modal');
            const captureBtn = document.getElementById('capture-photo-btn');
            const switchBtn = document.getElementById('switch-camera-btn');

            // Clics para abrir Cámara o Galería
            document.addEventListener('click', function (event) {
                if (!(event.target instanceof Element)) return;

                // 1. Abrir cámara del teléfono directamente (app nativa)
                const camBtn = event.target.closest('[data-trigger-camera]');
                if (camBtn) {
                    const box = camBtn.closest('[data-photo-uploader-box]');
                    const input = box ? box.querySelector('[data-camera-input]') : null;
                    if (input) input.click();
                    return;
                }

                // 2. Abrir explorador de galería o archivos
                const fileBtn = event.target.closest('[data-trigger-file]');
                if (fileBtn) {
                    const box = fileBtn.closest('[data-photo-uploader-box]');
                    const input = box ? box.querySelector('[data-file-input]') : null;
                    if (input) input.click();
                    return;
                }

                // 3. Abrir visor en vivo con retícula holográfica
                const liveBtn = event.target.closest('[data-trigger-live-view]');
                if (liveBtn) {
                    activeBox = liveBtn.closest('[data-photo-uploader-box]');
                    startLiveCamera();
                    return;
                }

                // 4. Limpiar / Quitar foto para tomar otra
                const clearBtn = event.target.closest('[data-clear-photo]');
                if (clearBtn) {
                    const box = clearBtn.closest('[data-photo-uploader-box]');
                    resetPhotoBox(box);
                    return;
                }
            });

            // Escuchar cambios en los inputs de archivos (Cámara o Galería)
            document.addEventListener('change', function (event) {
                const target = event.target;
                if (!(target instanceof HTMLInputElement)) return;

                if (target.matches('[data-camera-input], [data-file-input]')) {
                    const box = target.closest('[data-photo-uploader-box]');
                    if (target.files && target.files[0]) {
                        displayPreview(box, target.files[0]);
                    }
                } else if (target.matches('[data-url-input]')) {
                    const box = target.closest('[data-photo-uploader-box]');
                    if (target.value.trim().length > 7) {
                        displayUrlPreview(box, target.value.trim());
                    }
                }
            });

            function displayPreview(box, file) {
                if (!box) return;
                const previewWrap = box.querySelector('[data-photo-preview]');
                const previewImg = box.querySelector('[data-photo-preview-img]');
                const filenameText = box.querySelector('[data-photo-filename]');
                const metaText = box.querySelector('[data-photo-meta]');
                const optionsWrap = box.querySelector('[data-upload-options]');

                const sizeKb = (file.size / 1024).toFixed(1);
                filenameText.textContent = file.name || 'Fotografía de producto';
                metaText.textContent = `${sizeKb} KB · Lista para realzar con Estudio Neón IA`;

                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    if (previewWrap) previewWrap.classList.remove('hidden');
                    if (optionsWrap) optionsWrap.classList.add('hidden');
                };
                reader.readAsDataURL(file);
            }

            function displayUrlPreview(box, url) {
                if (!box) return;
                const previewWrap = box.querySelector('[data-photo-preview]');
                const previewImg = box.querySelector('[data-photo-preview-img]');
                const filenameText = box.querySelector('[data-photo-filename]');
                const metaText = box.querySelector('[data-photo-meta]');
                const optionsWrap = box.querySelector('[data-upload-options]');

                filenameText.textContent = 'Imagen desde enlace URL';
                metaText.textContent = 'Enlace remoto verificado · Listo para publicar';
                previewImg.src = url;

                if (previewWrap) previewWrap.classList.remove('hidden');
                if (optionsWrap) optionsWrap.classList.add('hidden');
            }

            function resetPhotoBox(box) {
                if (!box) return;
                const camInput = box.querySelector('[data-camera-input]');
                const fileInput = box.querySelector('[data-file-input]');
                const base64Input = box.querySelector('[data-base64-input]');
                const urlInput = box.querySelector('[data-url-input]');
                const previewWrap = box.querySelector('[data-photo-preview]');
                const previewImg = box.querySelector('[data-photo-preview-img]');
                const optionsWrap = box.querySelector('[data-upload-options]');

                if (camInput) camInput.value = '';
                if (fileInput) fileInput.value = '';
                if (base64Input) base64Input.value = '';
                if (urlInput) urlInput.value = '';
                if (previewImg) previewImg.src = '';

                if (previewWrap) previewWrap.classList.add('hidden');
                if (optionsWrap) optionsWrap.classList.remove('hidden');
            }

            // Iniciar visor de cámara en vivo
            async function startLiveCamera() {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    // Si el navegador no soporta streaming en vivo, abrir directamente la cámara nativa
                    if (activeBox) {
                        const camInput = activeBox.querySelector('[data-camera-input]');
                        if (camInput) camInput.click();
                    }
                    return;
                }

                modal.classList.remove('hidden');
                loading.classList.remove('hidden');

                try {
                    if (cameraStream) {
                        cameraStream.getTracks().forEach(t => t.stop());
                    }
                    cameraStream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: { ideal: currentFacingMode },
                            width: { ideal: 1280 },
                            height: { ideal: 960 }
                        },
                        audio: false
                    });
                    video.srcObject = cameraStream;
                    video.onloadedmetadata = function () {
                        loading.classList.add('hidden');
                    };
                } catch (err) {
                    console.warn('Error accediendo al visor en vivo, usando cámara del sistema:', err);
                    stopLiveCamera();
                    if (activeBox) {
                        const camInput = activeBox.querySelector('[data-camera-input]');
                        if (camInput) camInput.click();
                    }
                }
            }

            function stopLiveCamera() {
                if (cameraStream) {
                    cameraStream.getTracks().forEach(t => t.stop());
                    cameraStream = null;
                }
                if (video) video.srcObject = null;
                if (modal) modal.classList.add('hidden');
            }

            // Cambiar entre cámara delantera y trasera
            if (switchBtn) {
                switchBtn.addEventListener('click', function () {
                    currentFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';
                    startLiveCamera();
                });
            }

            // Capturar cuadro de video
            if (captureBtn) {
                captureBtn.addEventListener('click', function () {
                    if (!video.videoWidth || !video.videoHeight || !activeBox) {
                        return;
                    }

                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                    const dataUrl = canvas.toDataURL('image/jpeg', 0.92);

                    // Guardar en input base64 para envío al backend
                    const b64Input = activeBox.querySelector('[data-base64-input]');
                    if (b64Input) b64Input.value = dataUrl;

                    // Mostrar preview
                    const previewWrap = activeBox.querySelector('[data-photo-preview]');
                    const previewImg = activeBox.querySelector('[data-photo-preview-img]');
                    const filenameText = activeBox.querySelector('[data-photo-filename]');
                    const metaText = activeBox.querySelector('[data-photo-meta]');
                    const optionsWrap = activeBox.querySelector('[data-upload-options]');

                    filenameText.textContent = 'Foto capturada con cámara en vivo';
                    metaText.textContent = `${canvas.width}x${canvas.height} px · Lista para realzar con Estudio Neón IA`;
                    previewImg.src = dataUrl;

                    if (previewWrap) previewWrap.classList.remove('hidden');
                    if (optionsWrap) optionsWrap.classList.add('hidden');

                    stopLiveCamera();
                });
            }

            if (closeBtn) {
                closeBtn.addEventListener('click', stopLiveCamera);
            }
        })();
    </script>
@endPushOnce
