<?php

namespace App\Http\Controllers;

use App\Http\Requests\FacturaRequest;
use App\Models\Facturas;
use App\Services\FacturaService;

class FacturaController extends Controller
{
    public function __construct(private FacturaService $facturaService)
    {
    }

    public function index()
    {
        $facturas = $this->facturaService->listarTodo();

        return view('facturas.index', compact('facturas'));
    }

    public function create()
    {
        $productos = $this->facturaService->productosDisponibles();

        return view('facturas.create', compact('productos'));
    }

    public function store(FacturaRequest $request)
    {
        $factura = $this->facturaService->guardar($request->validated());

        return redirect()->route('facturas.index')
            ->with('success', 'Factura creada correctamente.');
    }

    public function show(Facturas $factura)
    {
        $factura->load('detalles.producto');

        return view('facturas.show', compact('factura'));
    }

    public function edit(Facturas $factura)
    {
        $factura = $this->facturaService->buscar($factura->id_factura);
        $productos = $this->facturaService->productosDisponibles();

        return view('facturas.edit', compact('factura', 'productos'));
    }

    public function update(FacturaRequest $request, Facturas $factura)
    {
        $this->facturaService->actualizar($factura, $request->validated());

        return redirect()->route('facturas.index')
            ->with('success', 'Factura actualizada correctamente.');
    }

    public function destroy(Facturas $factura)
    {
        $this->facturaService->eliminar($factura);

        return redirect()->route('facturas.index')
            ->with('success', 'Factura eliminada correctamente.');
    }
}
