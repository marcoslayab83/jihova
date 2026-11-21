<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MerchantRegistrationRequest extends FormRequest
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
            // Datos del Usuario / Representante Legal
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['required', 'string', 'max:30', Rule::unique('users', 'phone')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            // Datos Fiscales y Jurídicos del Comercio (Venezuela)
            'business_name' => ['required', 'string', 'max:200'],
            'legal_name' => ['required', 'string', 'max:255'],
            'rif' => ['required', 'string', 'regex:/^[JVGEjvge]-[0-9]{8,9}-[0-9]$/i', Rule::unique('stores', 'rif')],
            'iva_condition' => ['required', 'string', Rule::in(['ordinario', 'contribuyente_especial', 'formal'])],
            'category_id' => ['required', 'uuid', 'exists:categories,id'],
            'fiscal_address' => ['required', 'string', 'max:500'],

            // Sucursal Principal
            'branch_name' => ['required', 'string', 'max:150'],
            'branch_address' => ['required', 'string', 'max:500'],
            'branch_phone' => ['nullable', 'string', 'max:30'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'Ingrese el nombre completo del representante legal.',
            'email.unique' => 'Este correo electrónico ya se encuentra registrado.',
            'phone.unique' => 'Este número de teléfono ya está registrado.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'business_name.required' => 'Ingrese el nombre comercial de su tienda.',
            'legal_name.required' => 'Ingrese la razón social registrada en el registro mercantil.',
            'rif.required' => 'El RIF fiscal es obligatorio.',
            'rif.regex' => 'El formato del RIF debe ser válido (Ej: J-12345678-0, V-12345678-9).',
            'rif.unique' => 'Ya existe un comercio registrado con este RIF.',
            'iva_condition.required' => 'Seleccione la condición frente al IVA.',
            'category_id.required' => 'Seleccione la categoría de su comercio.',
            'fiscal_address.required' => 'Indique la dirección fiscal del comercio.',
            'branch_name.required' => 'Indique el nombre de la sucursal principal.',
            'branch_address.required' => 'Indique la dirección de la sucursal.',
        ];
    }
}
