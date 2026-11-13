<?php

use App\Http\Controllers\Api\V1\CatalogApiController;
use App\Http\Controllers\Api\V1\DriverApiController;
use App\Http\Controllers\Api\V1\OrderApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('/health', fn () => response()->json([
        'status' => 'ok',
        'service' => config('app.name'),
        'timestamp' => now()->toIso8601String(),
    ]))->name('health');

    Route::get('/categories', [CatalogApiController::class, 'categories'])->name('categories.index');
    Route::get('/stores', [CatalogApiController::class, 'stores'])->name('stores.index');
    Route::get('/stores/{store:slug}', [CatalogApiController::class, 'showStore'])->name('stores.show');
    Route::get('/search', [CatalogApiController::class, 'search'])->name('search');

    Route::middleware('auth')->group(function (): void {
        Route::post('/orders', [OrderApiController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}', [OrderApiController::class, 'show'])->name('orders.show');

        Route::middleware('role:driver,platform_admin')->prefix('driver')->name('driver.')->group(function (): void {
            Route::patch('/location', [DriverApiController::class, 'updateLocation'])->name('location.update');
            Route::post('/sub-orders/{subOrder}/advance', [DriverApiController::class, 'advance'])->name('sub-orders.advance');
        });
    });
});
