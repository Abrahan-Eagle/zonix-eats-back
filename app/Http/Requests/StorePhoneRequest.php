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

    public function rules(): array
    {
        return [
            'context' => 'nullable|string|in:'.Phone::CONTEXT_PERSONAL.','.Phone::CONTEXT_ADMIN,
            'operator_code_id' => 'required|exists:operator_codes,id',
            'number' => 'required|string|size:7|regex:/^\d{7}$/',
            'is_primary' => 'boolean',
        ];
    }

    protected function prepareForValidation(): void
    {
        $number = preg_replace('/\D/', '', (string) ($this->input('number', '') ?? ''));
        $this->merge([
            'number' => $number,
            'context' => $this->input('context', Phone::CONTEXT_PERSONAL),
        ]);
    }
}
