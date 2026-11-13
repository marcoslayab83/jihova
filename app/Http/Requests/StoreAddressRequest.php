<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:50'],
            'street_address' => ['required', 'string', 'max:300'],
            'reference' => ['nullable', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Asigna un alias a la dirección (ej. Casa).',
            'street_address.required' => 'La dirección de entrega es obligatoria.',
            'latitude.required' => 'Selecciona la ubicación en el mapa o una zona.',
            'longitude.required' => 'Selecciona la ubicación en el mapa o una zona.',
        ];
    }
}
