<?php
namespace App\Services;
 
use models
class\DetalleFacturaservices
{
    public function getDetalleFactura($idFactura)
    {
        // Lógica para obtener los detalles de la factura por su ID
        $detalleFactura = DetalleFactura::where('factura_id', $idFactura)->get();
        return $detalleFactura;
    }

    public function createDetalleFactura($data)
    {
        // Lógica para crear un nuevo detalle de factura
        $detalleFactura = new DetalleFactura();
        $detalleFactura->factura_id = $data['factura_id'];
        $detalleFactura->producto_id = $data['producto_id'];
        $detalleFactura->cantidad = $data['cantidad'];
        $detalleFactura->precio_unitario = $data['precio_unitario'];
        $detalleFactura->save();

        return $detalleFactura;
    }

    public function updateDetalleFactura($id, $data)
    {
        // Lógica para actualizar un detalle de factura existente
        $detalleFactura = DetalleFactura::find($id);
        if ($detalleFactura) {
            $detalleFactura->cantidad = $data['cantidad'] ?? $detalleFactura->cantidad;
            $detalleFactura->precio_unitario = $data['precio_unitario'] ?? $detalleFactura->precio_unitario;
            $detalleFactura->save();
            return $detalleFactura;
        }
        return null;
    }

    public function deleteDetalleFactura($id)
    {
        // Lógica para eliminar un detalle de factura
        return DetalleFactura::destroy($id);
    }

}