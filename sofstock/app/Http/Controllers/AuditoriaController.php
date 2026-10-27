<?php

namespace App\Http\Controllers;

use App\Repositories\AuditoriaRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditoriaController extends Controller
{
    protected AuditoriaRepository $auditoriaRepository;

    public function __construct(
        AuditoriaRepository $auditoriaRepository
    ) {
        $this->auditoriaRepository = $auditoriaRepository;
    }

    /**
     * Mostrar el historial de auditoría.
     */
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'usuario' => ['nullable', 'integer', 'min:1'],
            'accion' => ['nullable', 'string', 'max:50'],
            'modulo' => ['nullable', 'string', 'max:100'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => [
                'nullable',
                'date',
                'after_or_equal:fecha_desde',
            ],
        ]);

        $auditorias = $this->auditoriaRepository->listar(
            $filtros
        );

        return view('auditorias.index', compact('auditorias'));
    }

    /**
     * Mostrar el detalle de un registro de auditoría.
     */
    public function show(int $id): View
    {
        $auditoria = $this->auditoriaRepository->buscarPorId(
            $id
        );

        abort_if(
            $auditoria === null,
            404,
            'El registro de auditoría no existe.'
        );

        return view('auditorias.show', compact('auditoria'));
    }
}
