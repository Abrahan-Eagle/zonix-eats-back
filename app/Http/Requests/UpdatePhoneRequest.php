<?php

namespace App\Http\Requests;

use App\Models\Phone;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePhoneRequest extends FormRequest
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
            'context' => 'sometimes|string|in:'.implode(',', [
                Phone::CONTEXT_PERSONAL,
                Phone::CONTEXT_COMMERCE,
                Phone::CONTEXT_DELIVERY_COMPANY,
                Phone::CONTEXT_ADMIN,
            ]),
            'commerce_id' => 'nullable|exists:commerces,id',
            'delivery_company_id' => 'nullable|exists:delivery_companies,id',
            'operator_code_id' => 'sometimes|exists:operator_codes,id',
            'number' => 'sometimes|string|size:7|regex:/^\d{7}$/',
            'is_primary' => 'sometimes|boolean',
            'status' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'operator_code_id.exists' => 'El código de operador no es válido.',
            'number.size' => 'El número debe tener exactamente 7 dígitos.',
            'number.regex' => 'El número solo debe contener 7 dígitos.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('number')) {
            $number = preg_replace('/\D/', '', (string) $this->input('number'));
            $this->merge(['number' => $number]);
        }
    }
}
