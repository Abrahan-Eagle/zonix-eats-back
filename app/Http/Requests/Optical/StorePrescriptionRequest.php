<?php

namespace App\Http\Requests\Optical;

use Illuminate\Foundation\Http\FormRequest;

class StorePrescriptionRequest extends FormRequest
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
            'od_sphere' => ['nullable', 'numeric', 'between:-30,30'],
            'od_cylinder' => ['nullable', 'numeric', 'between:-10,10'],
            'od_axis' => ['nullable', 'integer', 'between:0,180'],
            'oi_sphere' => ['nullable', 'numeric', 'between:-30,30'],
            'oi_cylinder' => ['nullable', 'numeric', 'between:-10,10'],
            'oi_axis' => ['nullable', 'integer', 'between:0,180'],
            'addition' => ['nullable', 'numeric', 'between:0,4'],
            'pd' => ['nullable', 'numeric', 'between:40,80'],
            'pd_near' => ['nullable', 'numeric', 'between:40,80'],
            'document_id' => ['nullable', 'integer'],
        ];
    }
}
