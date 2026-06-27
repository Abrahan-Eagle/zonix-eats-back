<?php

namespace App\Http\Requests\Optical;

use Illuminate\Foundation\Http\FormRequest;

class StorePartnerPatientRequest extends FormRequest
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
            'email' => ['required', 'email', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'clinical_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
