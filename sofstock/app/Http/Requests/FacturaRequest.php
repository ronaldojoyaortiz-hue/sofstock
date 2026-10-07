<?php

namespace App\Http\Requests;

use App\Models\Facturas;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FacturaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Facturas|null $factura */
        $factura = $this->route('factura');

        return [
            'numero' => ['nullable', 'string', 'max:30', Rule::unique('facturas', 'numero')->ignore($factura?->id_factura, 'id_factura')],
            'cliente_nombre' => ['required', 'string', 'max:150'],
            'cliente_documento' => ['nullable', 'string', 'max:30'],
            'fecha' => ['required', 'date'],
            'descuento' => ['nullable', 'numeric', 'min:0'],
            'estado' => ['required', Rule::in(['borrador', 'emitida', 'pagada', 'anulada'])],
            'observaciones' => ['nullable', 'string'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.id_producto' => ['required', 'integer', 'exists:productos,id'],
            'detalles.*.producto_nombre' => ['nullable', 'string', 'max:150'],
            'detalles.*.cantidad' => ['nullable', 'numeric', 'min:0'],
            'detalles.*.precio_unitario' => ['nullable', 'numeric', 'gte:0'],
            'detalles.*.porcentaje_impuesto' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'detalles.*.descuento' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
