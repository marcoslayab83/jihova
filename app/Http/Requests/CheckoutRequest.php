<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
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
            'address_id' => ['nullable', 'uuid', Rule::exists('customer_addresses', 'id')->where('user_id', $this->user()?->id)],
            'new_address' => ['nullable', 'array'],
            'new_address.title' => ['required_without:address_id', 'nullable', 'string', 'max:50'],
            'new_address.street_address' => ['required_without:address_id', 'nullable', 'string', 'max:300'],
            'new_address.reference' => ['nullable', 'string', 'max:255'],
            'new_address.latitude' => ['required_without:address_id', 'nullable', 'numeric', 'between:-90,90'],
            'new_address.longitude' => ['required_without:address_id', 'nullable', 'numeric', 'between:-180,180'],
            'payment_method' => ['required', 'string', Rule::in(['card', 'mobile_pay', 'wallet', 'cash_on_delivery'])],
            'card_holder' => ['required_if:payment_method,card', 'nullable', 'string', 'max:100'],
            'card_number' => ['required_if:payment_method,card', 'nullable', 'string', 'min:12', 'max:24'],
            'card_expiry' => ['required_if:payment_method,card', 'nullable', 'string', 'max:7'],
            'card_cvv' => ['required_if:payment_method,card', 'nullable', 'string', 'min:3', 'max:4'],
            'mobile_phone' => ['required_if:payment_method,mobile_pay', 'nullable', 'string', 'max:30'],
            'customer_notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'address_id.exists' => 'La dirección seleccionada no es válida.',
            'new_address.title.required_without' => 'Indica un nombre para la dirección (ej. Casa u Oficina).',
            'new_address.street_address.required_without' => 'Ingresa la dirección exacta de entrega.',
            'payment_method.required' => 'Selecciona un método de pago.',
            'card_holder.required_if' => 'Ingresa el nombre del titular de la tarjeta.',
            'card_number.required_if' => 'Ingresa el número de tarjeta.',
            'card_expiry.required_if' => 'Ingresa la fecha de vencimiento.',
            'card_cvv.required_if' => 'Ingresa el código CVV.',
            'mobile_phone.required_if' => 'Ingresa el teléfono asociado al Pago Móvil.',
        ];
    }
}
