<?php

namespace App\Providers;

use App\Services\CartService;
use App\Services\GeoLocationService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());

        RateLimiter::for('login', function (Request $request): Limit {
            return Limit::perMinute(10)->by(Str::transliterate(
                Str::lower($request->string('email')->toString()).'|'.$request->ip()
            ));
        });

        View::composer('components.layouts.mall', function ($view): void {
            $cart = app(CartService::class);
            $geo = app(GeoLocationService::class);
            $user = auth()->user();

            $view->with('sharedLocation', $cart->activeLocation($user?->id));
            $view->with('sharedCartCount', array_sum(array_column($cart->rawItems(), 'quantity')));
            $view->with('sharedPresetZones', $geo->presetZones());
        });
    }
}
