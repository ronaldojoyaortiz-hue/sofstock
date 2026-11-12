<?php

namespace App\Http\Controllers;

use App\Services\ConsultasSistemaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ConsultasController extends Controller
{
    public function __construct(private ConsultasSistemaService $consultas) {}

    public function index(Request $request): View|RedirectResponse
    {
        $modulos = $this->consultas->modulosDisponibles($request->user());
        abort_if($modulos === [], 403);

        if ($request->filled('modulo')) {
            $modulo = collect($modulos)->firstWhere('tipo', $request->input('modulo'));
            abort_if($modulo === null, 403);

            return redirect()->to($modulo['ruta']);
        }

        return view('consultas.index', compact('modulos'));
    }

    public function listar(Request $request, string $tipoConsulta): View
    {
        $modulo = collect($this->consultas->modulosDisponibles($request->user()))
            ->firstWhere('tipo', $tipoConsulta);
        abort_if($modulo === null, 403);

        $definicionFiltros = $this->consultas->definicionFiltros($tipoConsulta);
        $nombresFiltros = array_column($definicionFiltros, 'nombre');
        $reglas = $this->consultas->reglasFiltros($tipoConsulta);
        $reglas['criterio'] = ['nullable', 'string', Rule::in($nombresFiltros)];
        $entrada = $request->validate($reglas);
        $criterio = $entrada['criterio'] ?? collect($nombresFiltros)
            ->first(fn (string $nombre): bool => isset($entrada[$nombre]) && $entrada[$nombre] !== '');
        $criterio ??= $nombresFiltros[0] ?? null;
        $valor = $criterio === null ? null : ($entrada[$criterio] ?? null);
        $filtros = $valor === null || $valor === '' ? [] : [$criterio => $valor];
        $opciones = $this->consultas->opcionesFiltros($tipoConsulta);
        $reportesDisponibles = $tipoConsulta !== 'reportes'
            || $this->consultas->reporteDisponible();
        $registros = $reportesDisponibles
            ? $this->consultas->listar(
                $tipoConsulta,
                $filtros,
                (int) $request->user()->getAuthIdentifier()
            )
            : null;
        $resumen = $tipoConsulta === 'reportes' && $reportesDisponibles
            ? $this->consultas->resumenReportes($filtros)
            : [];
        $filas = $registros
            ? $this->consultas->filas($tipoConsulta, $registros, $filtros)
            : null;
        if ($filas && $filtros !== []) {
            $filas->through(function (array $fila) use ($filtros): array {
                $fila['url'] .= '?'.http_build_query($filtros);

                return $fila;
            });
        }
        $columnas = $this->consultas->columnas($tipoConsulta, $filtros);

        return view('consultas.listado', compact(
            'tipoConsulta',
            'modulo',
            'filtros',
            'criterio',
            'definicionFiltros',
            'opciones',
            'reportesDisponibles',
            'resumen',
            'filas',
            'columnas'
        ));
    }

    public function detalle(Request $request, int|string $id, string $tipoConsulta): View
    {
        $modulo = collect($this->consultas->modulosDisponibles($request->user()))
            ->firstWhere('tipo', $tipoConsulta);
        abort_if($modulo === null, 403);

        $detalle = $this->consultas->detalle(
            $tipoConsulta,
            (int) $id,
            (int) $request->user()->getAuthIdentifier()
        );

        return view('consultas.detalle', compact('tipoConsulta', 'modulo', 'detalle'));
    }

    public function detalleReporte(string $tipoRegistro, int|string $id): View
    {
        $tipoConsulta = 'reportes';
        $detalle = $this->consultas->detalleReporte($tipoRegistro, (int) $id);
        $modulo = [
            'titulo' => 'Reportes financieros',
            'tipo' => 'reportes',
        ];

        return view('consultas.detalle', compact('tipoConsulta', 'modulo', 'detalle'));
    }
}
