<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RolStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_rol' => ['required', 'string', 'max:500'],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_rol.required' => 'El nombre del rol es obligatorio.',
            'nombre_rol.string' => 'El nombre del rol debe ser un texto válido.',
            'nombre_rol.max' => 'El nombre del rol no puede superar los 500 caracteres.',
            'descripcion.string' => 'La descripción debe ser un texto válido.',
            'descripcion.max' => 'La descripción no puede superar los 500 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre_rol' => 'nombre del rol',
            'descripcion' => 'descripción',
        ];
    }
}
