<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string|max:1000',
            'precio' => 'required|numeric|min:0|max:999999.99',
            'disponible' => 'boolean',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            // Campos adicionales si los tienes
            'stock' => 'nullable|integer|min:0',
            'categoria' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del producto es obligatorio',
            'nombre.max' => 'El nombre no puede superar los 255 caracteres',
            'descripcion.required' => 'La descripción es obligatoria',
            'descripcion.max' => 'La descripción no puede superar los 1000 caracteres',
            'precio.required' => 'El precio es obligatorio',
            'precio.numeric' => 'El precio debe ser un número',
            'precio.min' => 'El precio no puede ser negativo',
            'precio.max' => 'El precio no puede superar los 999,999.99',
            'disponible.boolean' => 'La disponibilidad debe ser verdadero o falso',
            'imagen.image' => 'El archivo debe ser una imagen',
            'imagen.mimes' => 'La imagen debe ser jpeg, png, jpg o gif',
            'imagen.max' => 'La imagen no puede superar los 2MB',
            'stock.integer' => 'El stock debe ser un número entero',
            'stock.min' => 'El stock no puede ser negativo'
        ];
    }
}
