<?php

namespace App\Http\Controllers;

use App\Services\FacturaService;
use Illuminate\View\View;

class FacturaController extends Controller
{
    public function __construct(private FacturaService $facturaService)
    {
    }

    public function index(): View
    {
        return view('facturas.index', [
            'facturas' => $this->facturaService->listarTodo(),
        ]);
    }

    public function show(int $factura): View
    {
        $registro = $this->facturaService->buscar($factura);
        $registro->load('detalles.producto');

        return view('facturas.show', ['factura' => $registro]);
    }

}
