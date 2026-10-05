<?php

namespace App\Services;

use App\Models\Facturas;
use App\Models\Producto;
use App\Repositories\FacturaRepository;
use Illuminate\Support\Facades\DB;

class FacturaService
{
    public function __construct(private FacturaRepository $facturaRepository) {}

    public function listarTodo()
    {
        return $this->facturaRepository->listarTodo();
    }

    public function buscar(int $id): Facturas
    {
        return $this->facturaRepository->buscar($id);
    }

    public function productosDisponibles()
    {
        return Producto::orderBy('nombre')->get();
    }

    public function guardar(array $datos): Facturas
    {
        $datos['fecha'] = now()->toDateString();

        return DB::transaction(function () use ($datos) {
            [$cabecera, $detalles] = $this->prepararDatos($datos);
            $factura = $this->facturaRepository->guardar($cabecera);
            $this->facturaRepository->reemplazarDetalles($factura, $detalles);

            return $factura;
        });
    }

    public function actualizar(Facturas $factura, array $datos): Facturas
    {
        return DB::transaction(function () use ($factura, $datos) {
            [$cabecera, $detalles] = $this->prepararDatos($datos);
            $factura = $this->facturaRepository->actualizar($factura, $cabecera);
            $this->facturaRepository->reemplazarDetalles($factura, $detalles);

            return $factura;
        });
    }

    public function eliminar(Facturas $factura): void
    {
        $this->facturaRepository->eliminar($factura);
    }

    private function prepararDatos(array $datos): array
    {
        if (empty($datos['numero'])) {
            $datos['numero'] = 'FAC-' . now()->format('YmdHis') . '-' . random_int(1000, 9999);
        }

        $detalles = $datos['detalles'];
        $subtotal = 0;
        $descuento = 0;
        $impuesto = 0;
        $detallesPreparados = [];

        foreach ($detalles as $detalle) {
            $producto = Producto::find($detalle['id_producto'] ?? null);
            $cantidad = (float) ($detalle['cantidad'] ?? 0);
            $precioUnitario = $producto
                ? (float) $producto->precio_base
                : (float) ($detalle['precio_unitario'] ?? 0);
            $subtotalDetalle = $cantidad * $precioUnitario;
            $descuentoDetalle = min(
                (float) ($detalle['descuento'] ?? 0),
                $subtotalDetalle
            );
            $porcentajeImpuesto = (float) ($detalle['porcentaje_impuesto'] ?? 0);
            $subtotalConDescuento = max($subtotalDetalle - $descuentoDetalle, 0);
            $impuestoDetalle = $subtotalConDescuento * ($porcentajeImpuesto / 100);

            $subtotal += $subtotalDetalle;
            $descuento += $descuentoDetalle;
            $impuesto += $impuestoDetalle;

            $detallesPreparados[] = [
                'id_producto' => $detalle['id_producto'],
                'producto_nombre' => $producto?->nombre ?? ($detalle['producto_nombre'] ?? ''),
                'cantidad' => $cantidad,
                'precio_unitario' => $precioUnitario,
                'porcentaje_impuesto' => $porcentajeImpuesto,
                'descuento' => $descuentoDetalle,
                'subtotal' => $subtotalDetalle,
            ];
        }

        $descuentoCabecera = $descuento > 0
            ? $descuento
            : min((float) ($datos['descuento'] ?? 0), $subtotal);

        $impuestoCabecera = $impuesto > 0
            ? $impuesto
            : (($subtotal - $descuentoCabecera) * (
                (float) ($datos['porcentaje_impuesto'] ?? 0) / 100
            ));

        unset($datos['detalles'], $datos['porcentaje_impuesto']);

        $cabecera = [
            ...$datos,
            'descuento' => $descuentoCabecera,
            'subtotal' => $subtotal,
            'impuesto' => $impuestoCabecera,
            'total' => $subtotal - $descuentoCabecera + $impuestoCabecera,
        ];

        return [$cabecera, $detallesPreparados];
    }
}
