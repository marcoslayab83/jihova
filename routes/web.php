<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DriverDashboardController;
use App\Http\Controllers\MallController;
use App\Http\Controllers\Merchant\CashShiftController;
use App\Http\Controllers\Merchant\PosController;
use App\Http\Controllers\Merchant\ThermalPrintController;
use App\Http\Controllers\MerchantDashboardController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

// Escaparate público del Centro Comercial Online
Route::get('/', [MallController::class, 'home'])->name('mall.home');
Route::get('/explorar', [MallController::class, 'explore'])->name('mall.explore');
Route::get('/tiendas', [MallController::class, 'stores'])->name('mall.stores');
Route::get('/tiendas/{store:slug}', [MallController::class, 'showStore'])->name('mall.stores.show');
Route::get('/producto/{store:slug}/{product:slug}', [MallController::class, 'showProduct'])
    ->scopeBindings()
    ->name('mall.products.show');
Route::get('/anuncio/{adBanner}/click', [MallController::class, 'clickAdBanner'])->name('mall.ads.click');
Route::post('/ubicacion', [MallController::class, 'updateLocation'])->name('mall.location.update');
Route::get('/progreso', [MallController::class, 'progress'])->name('mall.progress');

// Carrito de compras multi-tienda
Route::prefix('carrito')->name('cart.')->group(function (): void {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/items', [CartController::class, 'add'])->name('add');
    Route::patch('/items/{branchId}/{productId}', [CartController::class, 'update'])->name('update');
    Route::delete('/items/{branchId}/{productId}', [CartController::class, 'remove'])->name('remove');
    Route::delete('/', [CartController::class, 'clear'])->name('clear');
});

// Autenticación
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::get('/registro', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/registro', [AuthController::class, 'register']);
});

Route::post('/demo-login', [AuthController::class, 'demoLogin'])->name('demo.login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Rutas autenticadas de Cliente: Checkout, Pedidos y Direcciones
Route::middleware('auth')->group(function (): void {
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/mis-pedidos', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/pedidos/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/pedidos/{order}/estado', [OrderController::class, 'statusJson'])->name('orders.status');

    Route::prefix('mi-cuenta/direcciones')->name('addresses.')->group(function (): void {
        Route::get('/', [AddressController::class, 'index'])->name('index');
        Route::post('/', [AddressController::class, 'store'])->name('store');
        Route::patch('/{address}/predeterminada', [AddressController::class, 'setDefault'])->name('default');
        Route::delete('/{address}', [AddressController::class, 'destroy'])->name('destroy');
    });
});

// Panel de Comerciantes / Tiendas
Route::middleware(['auth', 'role:merchant_admin,merchant_staff,platform_admin'])
    ->prefix('panel-tienda')
    ->name('merchant.')
    ->group(function (): void {
        Route::get('/', [MerchantDashboardController::class, 'index'])->name('dashboard');
        Route::post('/sub-pedidos/{subOrder}/estado', [MerchantDashboardController::class, 'advanceSubOrder'])->name('sub-orders.advance');
        Route::post('/sub-pedidos/{subOrder}/despachar', [MerchantDashboardController::class, 'autoDispatch'])->name('sub-orders.dispatch');
        Route::post('/sucursales/{branch}/estado', [MerchantDashboardController::class, 'toggleBranch'])->name('branches.toggle');
        Route::patch('/inventario/{inventory}', [MerchantDashboardController::class, 'updateInventory'])->name('inventory.update');
        Route::post('/movimientos-inventario', [MerchantDashboardController::class, 'storeInventoryMovement'])->name('inventory-movements.store');
        Route::post('/ventas', [MerchantDashboardController::class, 'registerSale'])->name('sales.store');
        Route::patch('/productos/{product}/precios', [MerchantDashboardController::class, 'updateProductPricing'])->name('products.pricing');
        Route::post('/productos', [MerchantDashboardController::class, 'storeProduct'])->name('products.store');
        Route::patch('/productos/{product}', [MerchantDashboardController::class, 'updateProduct'])->name('products.update');
        Route::delete('/productos/{product}', [MerchantDashboardController::class, 'destroyProduct'])->name('products.destroy');

        // Suite IA de Edición de Imágenes
        Route::post('/productos/{product}/ia-mejorar', [MerchantDashboardController::class, 'aiEnhanceProduct'])->name('products.ai-enhance');
        Route::post('/ia/banner-generar', [MerchantDashboardController::class, 'aiGenerateBanner'])->name('ai.generate-banner');

        // Publicidad y Banners en Página Principal (Home)
        Route::post('/publicidad', [MerchantDashboardController::class, 'storeAdBanner'])->name('ads.store');
        Route::patch('/publicidad/{adBanner}/estado', [MerchantDashboardController::class, 'toggleAdBanner'])->name('ads.toggle');
        Route::delete('/publicidad/{adBanner}', [MerchantDashboardController::class, 'destroyAdBanner'])->name('ads.destroy');

        // Registro de Nueva Tienda / Sucursal
        Route::post('/tiendas', [MerchantDashboardController::class, 'storeStore'])->name('stores.store');

        // Registro de Ventas (Punto de Venta / POS / Mostrador)
        Route::post('/ventas', [MerchantDashboardController::class, 'storeSale'])->name('sales.store');
        Route::get('/ventas/{sale}', [MerchantDashboardController::class, 'showSale'])->name('sales.show');

        // Módulo Punto de Venta (POS) Omnicanal JIHOVA
        Route::prefix('pos')->name('pos.')->group(function (): void {
            Route::get('/', [PosController::class, 'index'])->name('index');
            Route::post('/ventas', [PosController::class, 'store'])->name('store');
            Route::get('/pedidos-en-vivo', [PosController::class, 'liveOrders'])->name('live-orders');
            Route::post('/sub-pedidos/{subOrder}/estado', [PosController::class, 'updateOrderStatus'])->name('order-status');

            // Cajas y Arqueos (Corte X y Corte Z)
            Route::post('/cajas/abrir', [CashShiftController::class, 'open'])->name('shifts.open');
            Route::post('/cajas/turnos/{shift}/cerrar', [CashShiftController::class, 'close'])->name('shifts.close');
            Route::get('/cajas/turnos/{shift}/corte-x', [CashShiftController::class, 'corteX'])->name('shifts.corte-x');
            Route::get('/cajas/turnos/{shift}/corte-z', [CashShiftController::class, 'corteZ'])->name('shifts.corte-z');

            // Impresión Térmica de Recibos y Comandas (80mm / 58mm)
            Route::get('/imprimir/orden/{order}', [ThermalPrintController::class, 'receipt'])->name('print.receipt');
            Route::get('/imprimir/comanda/{subOrder}', [ThermalPrintController::class, 'orderComanda'])->name('print.comanda');
        });
    });

// Panel de Repartidores / Delivery de la plataforma
Route::middleware(['auth', 'role:driver,platform_admin'])
    ->prefix('panel-repartidor')
    ->name('driver.')
    ->group(function (): void {
        Route::get('/', [DriverDashboardController::class, 'index'])->name('dashboard');
        Route::post('/turno', [DriverDashboardController::class, 'toggleShift'])->name('shift.toggle');
        Route::post('/ubicacion', [DriverDashboardController::class, 'updateLocation'])->name('location.update');
        Route::post('/envios/{subOrder}/tomar', [DriverDashboardController::class, 'claimSubOrder'])->name('sub-orders.claim');
        Route::post('/envios/{subOrder}/avanzar', [DriverDashboardController::class, 'advanceSubOrder'])->name('sub-orders.advance');
    });

// Panel de Administración del Centro Comercial
Route::middleware(['auth', 'role:platform_admin'])
    ->prefix('panel-admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::patch('/tiendas/{store}/estado', [AdminDashboardController::class, 'updateStoreStatus'])->name('stores.status');
        Route::post('/sub-pedidos/{subOrder}/asignar-repartidor', [AdminDashboardController::class, 'assignDriver'])->name('sub-orders.assign-driver');
    });
