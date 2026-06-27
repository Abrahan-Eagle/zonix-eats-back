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

    public function rules(): array
    {
        return [
            'context' => 'sometimes|string|in:'.Phone::CONTEXT_PERSONAL.','.Phone::CONTEXT_ADMIN,
            'operator_code_id' => 'sometimes|exists:operator_codes,id',
            'number' => 'sometimes|string|size:7|regex:/^\d{7}$/',
            'is_primary' => 'sometimes|boolean',
            'status' => 'sometimes|boolean',
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
