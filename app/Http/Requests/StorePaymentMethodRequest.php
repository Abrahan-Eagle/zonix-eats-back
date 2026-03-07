<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $currentYear = (int) date('Y');
        return [
            'type' => 'required|string|in:card,mobile_payment,cash,paypal,stripe,mercadopago,digital_wallet,bank_transfer,other',
            'bank_id' => 'nullable|exists:banks,id',
            'brand' => 'nullable|string|max:50',
            'last4' => 'nullable|string|size:4',
            'exp_month' => 'nullable|integer|between:1,12',
            'exp_year' => 'nullable|integer|min:' . ($currentYear - 1),
            'cardholder_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'owner_name' => 'nullable|string|max:255',
            'owner_id' => 'nullable|string|max:50',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'reference_info' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'El tipo de método de pago es obligatorio.',
            'type.in' => 'El tipo de método de pago no es válido.',
            'bank_id.exists' => 'El banco seleccionado no existe.',
            'last4.size' => 'Los últimos 4 dígitos deben ser exactamente 4.',
            'exp_month.between' => 'El mes de vencimiento debe estar entre 1 y 12.',
            'exp_year.min' => 'El año de vencimiento no es válido.',
        ];
    }
}
