<?php

namespace App\Http\Requests;

use App\Models\Phone;
use Illuminate\Foundation\Http\FormRequest;

class StorePhoneRequest extends FormRequest
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
        return [
            'context' => 'required|string|in:'.implode(',', [
                Phone::CONTEXT_PERSONAL,
                Phone::CONTEXT_COMMERCE,
                Phone::CONTEXT_DELIVERY_COMPANY,
                Phone::CONTEXT_ADMIN,
            ]),
            'commerce_id' => 'required_if:context,'.Phone::CONTEXT_COMMERCE.'|nullable|exists:commerces,id',
            'delivery_company_id' => 'required_if:context,'.Phone::CONTEXT_DELIVERY_COMPANY.'|nullable|exists:delivery_companies,id',
            'operator_code_id' => 'required|exists:operator_codes,id',
            'number' => 'required|string|size:7|regex:/^\d{7}$/',
            'is_primary' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'context.required' => 'El contexto de uso es obligatorio.',
            'context.in' => 'El contexto debe ser personal, commerce, delivery_company o admin.',
            'commerce_id.required_if' => 'Debes indicar el comercio para este teléfono.',
            'commerce_id.exists' => 'El comercio no es válido.',
            'delivery_company_id.required_if' => 'Debes indicar la empresa de delivery para este teléfono.',
            'delivery_company_id.exists' => 'La empresa de delivery no es válida.',
            'operator_code_id.required' => 'El código de operador es obligatorio.',
            'operator_code_id.exists' => 'El código de operador no es válido.',
            'number.required' => 'El número es obligatorio.',
            'number.size' => 'El número debe tener exactamente 7 dígitos.',
            'number.regex' => 'El número solo debe contener 7 dígitos.',
        ];
    }

    /**
     * Prepare the data for validation: strip non-digits from number.
     */
    protected function prepareForValidation(): void
    {
        $number = preg_replace('/\D/', '', (string) ($this->input('number', '') ?? ''));
        $this->merge(['number' => $number]);
        if (! $this->has('context')) {
            $this->merge(['context' => Phone::CONTEXT_PERSONAL]);
        }
    }
}
