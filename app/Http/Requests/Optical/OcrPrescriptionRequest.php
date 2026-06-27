<?php

namespace App\Http\Requests\Optical;

use Illuminate\Foundation\Http\FormRequest;

class OcrPrescriptionRequest extends FormRequest
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
            'patient_profile_id' => ['required', 'integer', 'exists:patient_profiles,id'],
            'document_id' => ['nullable', 'integer'],
            'od_sphere' => ['nullable', 'numeric'],
            'oi_sphere' => ['nullable', 'numeric'],
        ];
    }
}
