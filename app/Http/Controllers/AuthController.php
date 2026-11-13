<?php

namespace App\Http\Controllers;

use App\Enums\StoreStatus;
use App\Enums\UserRole;
use App\Http\Requests\RegisterRequest;
use App\Models\Category;
use App\Models\DriverProfile;
use App\Models\Store;
use App\Models\StoreBranch;
use App\Models\User;
use App\Services\GeoLocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login', [
            'demoEnabled' => app()->environment(['local', 'testing']),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Las credenciales ingresadas no coinciden con nuestros registros.',
                ]);
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();

        return redirect()->intended(route($user->role->dashboardRoute()));
    }

    public function showRegister(): View
    {
        return view('auth.register', [
            'categories' => Category::ordered()->get(),
        ]);
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $role = UserRole::from($validated['account_type']);

        $user = DB::transaction(function () use ($validated, $role): User {
            $user = User::create([
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => $validated['password'],
                'role' => $role,
            ]);

            if ($role === UserRole::MerchantAdmin) {
                $storeName = $validated['store_name'];
                $defaultCategory = Category::ordered()->first();

                $store = Store::create([
                    'owner_id' => $user->id,
                    'category_id' => $defaultCategory?->id,
                    'business_name' => $storeName,
                    'slug' => Str::slug($storeName).'-'.Str::lower(Str::random(4)),
                    'tagline' => 'Nueva tienda en JIHOVA',
                    'phone' => $validated['phone'],
                    'status' => StoreStatus::Pending,
                    'commission_rate' => 10.00,
                    'rating' => 5.0,
                ]);

                StoreBranch::create([
                    'store_id' => $store->id,
                    'branch_name' => $storeName.' · Sede Principal',
                    'address' => 'Centro Comercial Online JIHOVA, Local por asignar',
                    'latitude' => GeoLocationService::DEFAULT_LAT,
                    'longitude' => GeoLocationService::DEFAULT_LNG,
                    'delivery_radius_km' => 10.00,
                    'base_delivery_fee' => 2.50,
                    'avg_prep_time_minutes' => 15,
                    'is_open' => true,
                ]);
            }

            if ($role === UserRole::Driver) {
                DriverProfile::create([
                    'user_id' => $user->id,
                    'vehicle_type' => $validated['vehicle_type'],
                    'license_plate' => $validated['license_plate'] ?: null,
                    'current_latitude' => GeoLocationService::DEFAULT_LAT,
                    'current_longitude' => GeoLocationService::DEFAULT_LNG,
                    'is_active' => true,
                    'is_available' => true,
                    'last_ping_at' => now(),
                ]);
            }

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route($user->role->dashboardRoute())
            ->with('status', '¡Bienvenido a JIHOVA! Tu cuenta fue creada.');
    }

    public function demoLogin(Request $request): RedirectResponse
    {
        abort_unless(app()->environment(['local', 'testing']), 403);

        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $user = User::query()->where('email', $validated['email'])->firstOrFail();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route($user->role->dashboardRoute()))
            ->with('status', "Sesión iniciada como {$user->full_name} ({$user->role->label()}).");
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('mall.home');
    }
}
