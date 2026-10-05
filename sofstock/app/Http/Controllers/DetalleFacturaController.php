<?php

namespace App\Http\Controllers;

use App\Models\DetalleFactura;
use App\Models\Facturas;
use App\Models\Producto;
use Illuminate\Http\Request;

class DetalleFacturaController extends Controller
{
    public function index()
    {
        $detalleFacturas = DetalleFactura::with(['factura', 'producto'])->latest('id_detalle_factura')->get();

        return view('detallefactura.index', compact('detalleFacturas'));
    }

    public function create()
    {
        $facturas = Facturas::orderBy('fecha')->get();
        $productos = Producto::orderBy('nombre')->get();

        return view('detallefactura.create', compact('facturas', 'productos'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_factura' => ['required', 'integer', 'exists:facturas,id_factura'],
            'id_producto' => ['required', 'integer', 'exists:productos,id'],
            'producto_nombre' => ['nullable', 'string', 'max:150'],
            'cantidad' => ['required', 'numeric', 'gt:0'],
            'precio_unitario' => ['required', 'numeric', 'gte:0'],
            'porcentaje_impuesto' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'descuento' => ['nullable', 'numeric', 'min:0'],
        ]);

        $producto = Producto::findOrFail($data['id_producto']);
        $data['producto_nombre'] = $data['producto_nombre'] ?? $producto->nombre;
        $data['cantidad'] = (float) $data['cantidad'];
        $data['precio_unitario'] = (float) $data['precio_unitario'];
        $data['porcentaje_impuesto'] = (float) ($data['porcentaje_impuesto'] ?? 0);
        $data['descuento'] = (float) ($data['descuento'] ?? 0);
        $subtotalLinea = $data['cantidad'] * $data['precio_unitario'];
        $data['subtotal'] = $subtotalLinea;

        DetalleFactura::create($data);

        return redirect()->route('detallefacturas.index')
            ->with('success', 'Detalle de factura creado correctamente.');
    }

    public function show(DetalleFactura $detallefactura)
    {
        $detallefactura->load(['factura', 'producto']);

        return view('detallefactura.show', compact('detallefactura'));
    }

    public function edit(DetalleFactura $detallefactura)
    {
        $detallefactura->load(['factura', 'producto']);
        $facturas = Facturas::orderBy('fecha')->get();
        $productos = Producto::orderBy('nombre')->get();

        return view('detallefactura.edit', compact('detallefactura', 'facturas', 'productos'));
    }

    public function update(Request $request, DetalleFactura $detallefactura)
    {
        $data = $request->validate([
            'id_factura' => ['required', 'integer', 'exists:facturas,id_factura'],
            'id_producto' => ['required', 'integer', 'exists:productos,id'],
            'producto_nombre' => ['nullable', 'string', 'max:150'],
            'cantidad' => ['required', 'numeric', 'gt:0'],
            'precio_unitario' => ['required', 'numeric', 'gte:0'],
            'porcentaje_impuesto' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'descuento' => ['nullable', 'numeric', 'min:0'],
        ]);

        $producto = Producto::findOrFail($data['id_producto']);
        $data['producto_nombre'] = $data['producto_nombre'] ?? $producto->nombre;
        $data['cantidad'] = (float) $data['cantidad'];
        $data['precio_unitario'] = (float) $data['precio_unitario'];
        $data['porcentaje_impuesto'] = (float) ($data['porcentaje_impuesto'] ?? 0);
        $data['descuento'] = (float) ($data['descuento'] ?? 0);
        $data['subtotal'] = $data['cantidad'] * $data['precio_unitario'];

        $detallefactura->update($data);

        return redirect()->route('detallefacturas.index')
            ->with('success', 'Detalle de factura actualizado correctamente.');
    }

    public function destroy(DetalleFactura $detallefactura)
    {
        $detallefactura->delete();

        return redirect()->route('detallefacturas.index')
            ->with('success', 'Detalle de factura eliminado correctamente.');
    }
}
