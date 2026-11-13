<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Enums\VehicleType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['required', 'string', 'max:30', Rule::unique('users', 'phone')],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'account_type' => ['required', 'string', Rule::in([
                UserRole::Customer->value,
                UserRole::MerchantAdmin->value,
                UserRole::Driver->value,
            ])],
            'store_name' => ['required_if:account_type,merchant_admin', 'nullable', 'string', 'max:200'],
            'vehicle_type' => ['required_if:account_type,driver', 'nullable', Rule::enum(VehicleType::class)],
            'license_plate' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'Ingresa tu nombre completo.',
            'email.unique' => 'Este correo ya está registrado.',
            'phone.unique' => 'Este número telefónico ya está registrado.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'store_name.required_if' => 'Ingresa el nombre comercial de tu tienda.',
            'vehicle_type.required_if' => 'Selecciona el tipo de vehículo para repartos.',
        ];
    }
}
