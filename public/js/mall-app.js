(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const toastContainer = () => document.getElementById('mall-toast-container');

    function showToast(message, isError = false) {
        const container = toastContainer();
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `px-4 py-3 rounded-2xl shadow-2xl text-sm font-semibold border flex items-center gap-2.5 transition-all duration-300 backdrop-blur-xl ${
            isError
                ? 'bg-[#FF3366]/95 text-white border-[#ff7096]/60 shadow-[#FF3366]/30'
                : 'bg-[#121728]/95 text-[#F3F4F6] border-[#00F0FF]/45 shadow-[#00F0FF]/20'
        }`;
        toast.textContent = (isError ? '⚠️ ' : '✨ ') + message;
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(8px)';
            setTimeout(() => toast.remove(), 300);
        }, 3200);
    }

    function updateCartBadges(count) {
        document.querySelectorAll('[data-cart-badge]').forEach((el) => {
            el.textContent = String(count);
            el.classList.toggle('hidden', Number(count) <= 0);
        });
    }

    function formatMoney(value) {
        return '$' + Number(value || 0).toFixed(2);
    }

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function renderCartDrawer(cart) {
        const body = document.getElementById('cart-drawer-body');
        const footer = document.getElementById('cart-drawer-footer');
        if (!body || !footer) return;

        updateCartBadges(cart.items_count || 0);

        if (!cart.store_groups || cart.store_groups.length === 0) {
            body.innerHTML = `
                <div class="text-center py-14 px-4 space-y-3">
                    <div class="w-16 h-16 mx-auto rounded-3xl bg-gradient-to-br from-[#6C28FE]/30 to-[#00F0FF]/25 border border-white/15 text-[#00F0FF] flex items-center justify-center text-3xl shadow-inner">🛍️</div>
                    <h4 class="font-display font-bold text-white">Tu carrito de JIHOVA está vacío</h4>
                    <p class="text-sm text-slate-400">Agrega productos de una o varias tiendas y recíbelos en casa.</p>
                </div>
            `;
            footer.classList.add('hidden');
            return;
        }

        const groupsHtml = cart.store_groups.map((group) => {
            const itemsHtml = group.items.map((item) => `
                <div class="flex items-center justify-between gap-3 py-2.5 border-b border-white/10 last:border-0">
                    <div class="flex items-center gap-3 min-w-0">
                        <img src="${item.image_url || '/images/catalog/moda.jpg'}" alt="${escapeHtml(item.product_name)}" class="w-12 h-12 rounded-xl object-cover bg-[#171E33] shrink-0 border border-white/10">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-white truncate">${escapeHtml(item.product_name)}</p>
                            <p class="text-xs text-slate-400">${formatMoney(item.unit_price)} c/u</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <button type="button" data-cart-qty="${group.branch_id}|${item.product_id}|${item.quantity - 1}" class="w-7 h-7 rounded-lg border border-white/15 bg-[#0B0E17] text-slate-200 hover:border-[#00F0FF] text-sm font-bold cursor-pointer">-</button>
                        <span class="w-6 text-center text-sm font-bold text-white">${item.quantity}</span>
                        <button type="button" data-cart-qty="${group.branch_id}|${item.product_id}|${item.quantity + 1}" class="w-7 h-7 rounded-lg border border-white/15 bg-[#0B0E17] text-slate-200 hover:border-[#00F0FF] text-sm font-bold cursor-pointer">+</button>
                    </div>
                </div>
            `).join('');

            return `
                <div class="bg-[#121728] border border-white/10 rounded-2xl p-3.5 space-y-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-[#00F0FF]">${escapeHtml(group.store_name)}</p>
                            <p class="text-xs text-slate-400">${escapeHtml(group.branch_name)} · ~${group.eta_minutes} min</p>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-[#6C28FE]/20 border border-[#6C28FE]/40 text-violet-200">
                            Envío ${formatMoney(group.delivery_fee)}
                        </span>
                    </div>
                    <div class="bg-[#0B0E17]/65 rounded-xl px-3 border border-white/5">
                        ${itemsHtml}
                    </div>
                </div>
            `;
        }).join('');

        body.innerHTML = `<div class="space-y-4">${groupsHtml}</div>`;
        footer.classList.remove('hidden');

        const itemsTotalEl = document.getElementById('cart-drawer-items-total');
        const deliveryTotalEl = document.getElementById('cart-drawer-delivery-total');
        const grandTotalEl = document.getElementById('cart-drawer-grand-total');
        if (itemsTotalEl) itemsTotalEl.textContent = formatMoney(cart.total_items_amount);
        if (deliveryTotalEl) deliveryTotalEl.textContent = formatMoney(cart.total_delivery_fee + cart.platform_fee);
        if (grandTotalEl) grandTotalEl.textContent = formatMoney(cart.grand_total);
    }

    async function loadCartDrawer() {
        try {
            const res = await fetch('/carrito', { headers: { Accept: 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            if (data.cart) renderCartDrawer(data.cart);
        } catch (err) {
            console.error(err);
        }
    }

    function openCartDrawer() {
        const drawer = document.getElementById('cart-drawer');
        if (!drawer) return;
        drawer.classList.remove('hidden');
        loadCartDrawer();
    }

    function closeCartDrawer() {
        const drawer = document.getElementById('cart-drawer');
        if (!drawer) return;
        drawer.classList.add('hidden');
    }

    document.addEventListener('click', async (event) => {
        const userMenuBtn = event.target.closest('[data-user-menu-btn]');
        const userMenuDropdown = document.getElementById('user-menu-dropdown');
        if (userMenuBtn && userMenuDropdown) {
            event.preventDefault();
            userMenuDropdown.classList.toggle('hidden');
            return;
        }
        if (userMenuDropdown && !event.target.closest('#user-menu-wrapper')) {
            userMenuDropdown.classList.add('hidden');
        }

        const openBtn = event.target.closest('[data-open-cart]');
        if (openBtn) {
            event.preventDefault();
            openCartDrawer();
            return;
        }

        if (event.target.closest('[data-close-cart]')) {
            closeCartDrawer();
            return;
        }

        const openLocBtn = event.target.closest('[data-open-location-modal]');
        if (openLocBtn) {
            document.getElementById('location-modal')?.classList.remove('hidden');
            return;
        }

        if (event.target.closest('[data-close-location-modal]')) {
            document.getElementById('location-modal')?.classList.add('hidden');
            return;
        }

        const qtyBtn = event.target.closest('[data-cart-qty]');
        if (qtyBtn) {
            const [branchId, productId, qty] = qtyBtn.getAttribute('data-cart-qty').split('|');
            try {
                const res = await fetch(`/carrito/items/${branchId}/${productId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ quantity: Number(qty) }),
                });
                const data = await res.json();
                if (!res.ok) {
                    showToast(data.message || 'No se pudo actualizar la cantidad.', true);
                    return;
                }
                if (data.cart) renderCartDrawer(data.cart);
            } catch (err) {
                showToast('Error de conexión al actualizar el carrito.', true);
            }
        }
    });

    // Intercept AJAX add-to-cart forms
    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('form[data-ajax-cart]');
        if (!form) return;

        event.preventDefault();
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Agregando...';
        }

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: formData,
            });
            const data = await res.json();

            if (!res.ok) {
                const firstErr = data.errors ? Object.values(data.errors).flat()[0] : data.message;
                showToast(firstErr || 'No se pudo agregar al carrito.', true);
                return;
            }

            if (data.cart) {
                updateCartBadges(data.cart.items_count || 0);
                renderCartDrawer(data.cart);
            }
            if (submitBtn) {
                submitBtn.innerHTML = '✓ Agregado';
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                }, 1200);
            }
            showToast(data.message || 'Producto agregado al carrito.');
        } catch (err) {
            form.submit();
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                if (submitBtn.innerHTML === 'Agregando...') {
                    submitBtn.innerHTML = originalText;
                }
            }
        }
    });

    // Interactive Hero Spotlight Carousel
    const slides = document.querySelectorAll('[data-hero-slide]');
    const dots = document.querySelectorAll('[data-hero-dot]');
    if (slides.length > 1) {
        let currentIndex = 0;
        let timer = null;

        function showSlide(index) {
            currentIndex = (index + slides.length) % slides.length;
            slides.forEach((slide, idx) => {
                const isActive = idx === currentIndex;
                slide.classList.toggle('opacity-100', isActive);
                slide.classList.toggle('opacity-0', !isActive);
                slide.classList.toggle('pointer-events-none', !isActive);
                slide.classList.toggle('z-10', isActive);
            });
            dots.forEach((dot, idx) => {
                const isActive = idx === currentIndex;
                dot.classList.toggle('bg-[#FF3366]', isActive);
                dot.classList.toggle('text-white', isActive);
                dot.classList.toggle('border-[#FF3366]', isActive);
                dot.classList.toggle('shadow-lg', isActive);
                dot.classList.toggle('bg-[#121728]/80', !isActive);
                dot.classList.toggle('text-slate-300', !isActive);
                dot.classList.toggle('border-white/10', !isActive);
            });
        }

        dots.forEach((dot, idx) => {
            dot.addEventListener('click', () => {
                showSlide(idx);
                if (timer) clearInterval(timer);
                timer = setInterval(() => showSlide(currentIndex + 1), 4800);
            });
        });

        timer = setInterval(() => showSlide(currentIndex + 1), 4800);
    }

    // Browser Geolocation helper
    const gpsBtn = document.getElementById('use-browser-gps-btn');
    if (gpsBtn) {
        gpsBtn.addEventListener('click', () => {
            if (!navigator.geolocation) {
                showToast('Tu navegador no soporta geolocalización.', true);
                return;
            }
            gpsBtn.disabled = true;
            gpsBtn.textContent = 'Detectando ubicación...';
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const latInput = document.querySelector('#location-custom-form input[name="latitude"]');
                    const lngInput = document.querySelector('#location-custom-form input[name="longitude"]');
                    const labelInput = document.querySelector('#location-custom-form input[name="label"]');
                    if (latInput) latInput.value = pos.coords.latitude.toFixed(6);
                    if (lngInput) lngInput.value = pos.coords.longitude.toFixed(6);
                    if (labelInput && !labelInput.value) labelInput.value = 'Mi ubicación GPS actual';
                    gpsBtn.disabled = false;
                    gpsBtn.textContent = '📍 Coordenadas detectadas';
                    showToast('Coordenadas GPS detectadas. Presiona Guardar ubicación.');
                },
                () => {
                    gpsBtn.disabled = false;
                    gpsBtn.textContent = '📍 Usar mi GPS';
                    showToast('No pudimos acceder al GPS. Elige una zona de la lista.', true);
                }
            );
        });
    }
})();
