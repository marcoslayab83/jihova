<?php

namespace App\Http\Controllers;

use App\Enums\StoreStatus;
use App\Enums\UserRole;
use App\Http\Requests\MerchantRegistrationRequest;
use App\Models\Category;
use App\Models\Store;
use App\Models\StoreBranch;
use App\Models\StoreUser;
use App\Models\User;
use App\Services\GeoLocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MerchantRegistrationController extends Controller
{
    /**
     * Muestra el formulario de registro comercial para nuevos comercios aliados
     */
    public function show(): View
    {
        return view('auth.merchant-register', [
            'categories' => Category::ordered()->get(),
        ]);
    }

    /**
     * Procesa la solicitud de afiliación comercial y crea la tienda en estado pending
     */
    public function register(MerchantRegistrationRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($validated): User {
            // 1. Crear el usuario con rol MerchantAdmin
            $user = User::create([
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => $validated['password'],
                'role' => UserRole::MerchantAdmin,
            ]);

            // 2. Crear la tienda en estado PENDING con datos fiscales de Venezuela
            $isSpecialTaxpayer = $validated['iva_condition'] === 'contribuyente_especial';
            $slug = Str::slug($validated['business_name']).'-'.Str::lower(Str::random(4));

            $store = Store::create([
                'owner_id' => $user->id,
                'category_id' => $validated['category_id'],
                'business_name' => $validated['business_name'],
                'legal_name' => $validated['legal_name'],
                'rif' => strtoupper($validated['rif']),
                'iva_condition' => $validated['iva_condition'],
                'is_special_taxpayer' => $isSpecialTaxpayer,
                'fiscal_address' => $validated['fiscal_address'],
                'slug' => $slug,
                'phone' => $validated['phone'],
                'status' => StoreStatus::Pending, // En espera de validación del administrador
                'commission_rate' => 10.00,
                'rating' => 5.0,
                'bcv_rate' => 50.0000,
            ]);

            // 3. Crear la sucursal principal
            $branch = StoreBranch::create([
                'store_id' => $store->id,
                'branch_name' => $validated['branch_name'],
                'address' => $validated['branch_address'],
                'latitude' => GeoLocationService::DEFAULT_LAT,
                'longitude' => GeoLocationService::DEFAULT_LNG,
                'delivery_radius_km' => 10.00,
                'base_delivery_fee' => 2.50,
                'avg_prep_time_minutes' => 15,
                'is_open' => true,
                'pos_enabled' => true,
                'invoice_printer_type' => '80mm',
            ]);

            // 4. Crear el registro en store_users asignando al usuario como OWNER
            StoreUser::create([
                'store_id' => $store->id,
                'user_id' => $user->id,
                'role' => 'owner',
                'store_branch_id' => $branch->id,
                'is_active' => true,
            ]);

            return $user;
        });

        // Autenticar al usuario de inmediato
        Auth::login($user);

        return redirect()->route('merchant.dashboard')->with('status', '¡Solicitud comercial registrada exitosamente! Tu tienda se encuentra en estado pendiente de verificación por el equipo administrativo de JIHOVA.');
    }
}
