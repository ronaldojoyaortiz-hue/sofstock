<?php

namespace App\Http\Controllers;

use App\Http\Requests\FacturaRequest;
use App\Models\Facturas;
use App\Services\FacturaService;

class DetalleFacturaController extends Controller
{
    public function __construct(private FacturaService $facturaService)
    {
    }

    public function index()
    {
        return view('factura.index', [
            'facturas' => $this->facturaService->listarTodo(),
        ]);
    }

    public function create()
    {
        return view('factura.create', [
            'productos' => $this->facturaService->productosDisponibles(),
        ]);
    }

    public function store(FacturaRequest $request)
    {
        $this->facturaService->guardar($request->validated());

        return redirect()->route('detallefacturas.index')
            ->with('success', 'Factura creada correctamente.');
    }

    public function show($id)
    {
        $factura = $this->facturaService->buscar((int) $id);
        $factura->load('detalles.producto');

        return view('detallefacturas.show', compact('factura'));
    }

    public function edit($id)
    {
        $factura = $this->facturaService->buscar((int) $id);
        $factura->load('detalles.producto');
        $productos = $this->facturaService->productosDisponibles();

        return view('factura.edit', compact('factura', 'productos'));
    }

    public function update(FacturaRequest $request, $id)
    {
        $factura = $this->facturaService->buscar((int) $id);
        $this->facturaService->actualizar($factura, $request->validated());

        return redirect()->route('detallefacturas.index')
            ->with('success', 'Factura actualizada correctamente.');
    }

    public function destroy($id)
    {
        $factura = $this->facturaService->buscar((int) $id);
        $this->facturaService->eliminar($factura);

        return redirect()->route('detallefacturas.index')
            ->with('success', 'Factura eliminada correctamente.');
    }
}
