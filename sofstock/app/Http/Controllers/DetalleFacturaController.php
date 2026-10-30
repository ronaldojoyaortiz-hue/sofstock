<?php

namespace App\Http\Controllers;

use App\Http\Requests\FacturaRequest;
use App\Models\Facturas;
use App\Services\FacturaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DetalleFacturaController extends Controller
{
    public function __construct(private FacturaService $facturaService)
    {
    }

    public function index(): View
    {
        return view('facturas.detalle-index', [
            'facturas' => $this->facturaService->listarTodo(),
        ]);
    }

    public function create(): View
    {
        return view('facturas.create', [
            'productos' => $this->facturaService->productosDisponibles(),
        ]);
    }

    public function store(FacturaRequest $request): RedirectResponse
    {
        $factura = $this->facturaService->guardar($request->validated());

        return redirect()->route('facturas.show', $factura->id_factura)
            ->with('success', 'Factura creada correctamente.');
    }

    public function edit(Facturas $detallefactura): View|RedirectResponse
    {
        if ($detallefactura->estado === 'pagada') {
            return redirect()->route('facturas.show', $detallefactura->id_factura)
                ->with('error', 'No se puede editar una factura pagada.');
        }

        $detallefactura->load('detalles.producto');

        return view('facturas.edit', [
            'factura' => $detallefactura,
            'productos' => $this->facturaService->productosDisponibles(),
        ]);
    }

    public function update(FacturaRequest $request, Facturas $detallefactura): RedirectResponse
    {
        if ($detallefactura->estado === 'pagada') {
            return redirect()->route('facturas.show', $detallefactura->id_factura)
                ->with('error', 'No se puede editar una factura pagada.');
        }

        $this->facturaService->actualizar($detallefactura, $request->validated());

        return redirect()->route('facturas.show', $detallefactura->id_factura)
            ->with('success', 'Factura actualizada correctamente.');
    }

    public function destroy(Facturas $detallefactura): RedirectResponse
    {
        $this->facturaService->eliminar($detallefactura);

        return redirect()->route('detallefacturas.index')
            ->with('success', 'Factura eliminada correctamente.');
    }
}
