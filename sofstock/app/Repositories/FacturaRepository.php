<?php

namespace App\Repositories;

use App\Models\Facturas;
use Illuminate\Support\Facades\Schema;

class FacturaRepository
{
    public function listarTodo()
    {
        return Facturas::latest('fecha')->latest('id_factura')->get();
    }

    public function buscar(int $id): Facturas
    {
        return Facturas::with('detalles.producto')->findOrFail($id);
    }

    public function guardar(array $datos): Facturas
    {
        $factura = Facturas::create($datos);

        if (empty($factura->numero)) {
            $factura->numero = (string) $factura->id_factura;
            $factura->save();
        }

        return $factura;
    }

    public function actualizar(Facturas $factura, array $datos): Facturas
    {
        if (empty($datos['numero'])) {
            $datos['numero'] = (string) $factura->id_factura;
        }

        $factura->update($datos);

        return $factura;
    }

    public function reemplazarDetalles(Facturas $factura, array $detalles): void
    {
        $factura->detalles()->delete();

        $columnasDisponibles = Schema::getColumnListing('detalle_facturas');
        $columnasPermitidas = array_flip($columnasDisponibles);

        $detallesFiltrados = array_map(function (array $detalle) use ($columnasPermitidas) {
            return array_intersect_key($detalle, $columnasPermitidas);
        }, $detalles);

        if ($detallesFiltrados !== []) {
            $factura->detalles()->createMany($detallesFiltrados);
        }
    }

    public function eliminar(Facturas $factura): void
    {
        $factura->delete();
    }
}
