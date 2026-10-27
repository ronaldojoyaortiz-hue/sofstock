<?php

namespace App\Http\Controllers;

use App\Models\Facturas;
use App\Services\FacturaService;

class FacturaController extends Controller
{
    public function __construct(private FacturaService $facturaService)
    {
    }

    public function index()
    {
        return view('facturas.index', [
            'facturas' => $this->facturaService->listarTodo(),
        ]);
    }

    public function show($id)
    {
        $factura = $this->facturaService->buscar((int) $id);
        $factura->load('detalles.producto');

        return view('facturas.show', compact('factura'));
    }
}
