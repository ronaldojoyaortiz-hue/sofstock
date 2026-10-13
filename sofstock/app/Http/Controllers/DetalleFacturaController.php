<?php

namespace App\Http\Controllers;

use App\Services\FacturaService;

class DetalleFacturaController extends Controller
{
    public function __construct(private FacturaService $facturaService)
    {
    }

    public function index()
    {
        return view('detallefactura.index', [
            'facturas' => $this->facturaService->listarTodo(),
        ]);
    }
}
