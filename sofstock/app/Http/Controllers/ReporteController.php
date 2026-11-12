<?php

namespace App\Http\Controllers;

use App\Models\EgresoMensual;
use App\Models\CambioPresupuesto;
use App\Models\Facturas;
use App\Models\MermaProducto;
use App\Models\Producto;
use App\Models\PresupuestoMensual;
use App\Models\ReporteGenerado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class ReporteController extends Controller
{
    public function index(Request $request): View
    {
        $tablasFinancierasListas = Schema::hasTable('presupuestos_mensuales')
            && Schema::hasTable('historial_presupuestos')
            && Schema::hasTable('egresos_mensuales')
            && Schema::hasColumn('egresos_mensuales', 'id_producto')
            && Schema::hasColumn('egresos_mensuales', 'id_proveedor')
            && Schema::hasTable('mermas_productos')
            && Schema::hasTable('reportes_generados');

        return view('reportes.index', [
            'tablasFinancierasListas' => $tablasFinancierasListas,
            'reportes' => Schema::hasTable('reportes_generados')
                ? ReporteGenerado::query()->latest()->paginate(10)
                : collect(),
            'productos' => Producto::query()->where('activo', true)->orderBy('nombre')->get(['id', 'nombre', 'stock_actual', 'precio_base']),
            'fechaPredeterminada' => now()->toDateString(),
        ]);
    }

    public function generar(Request $request): RedirectResponse
    {
        $filtros = $this->validarFiltros($request);
        $rango = $this->construirRango($filtros);

        if (! $this->tablasFinancierasDisponibles()) {
            return redirect()->route('reports.index')->with('error', 'No se puede guardar el reporte todavía. Aplica las migraciones pendientes de SofStock y vuelve a intentarlo.');
        }

        $desde = $rango['desde']->toDateString();
        $hasta = $rango['hasta']->toDateString();

        $reporte = ReporteGenerado::query()->create([
            'tipo_reporte' => $rango['periodo'],
            'fecha_desde' => $desde,
            'fecha_hasta' => $hasta,
            'id_usuario' => $request->user()->getAuthIdentifier(),
            'datos' => $this->datosReporte($rango),
        ]);

        return redirect()->route('reports.show', $reporte)
            ->with('success', 'El reporte quedó guardado.');
    }

    public function show(ReporteGenerado $reporte): View
    {
        return view('reportes.show', compact('reporte'));
    }

    public function editar(ReporteGenerado $reporte): View
    {
        $referencia = match ($reporte->tipo_reporte) {
            'semanal', 'diario' => $reporte->fecha_desde->toDateString(),
            'anual' => $reporte->fecha_desde->format('Y'),
            default => $reporte->fecha_desde->format('Y-m'),
        };

        return view('reportes.edit', compact('reporte', 'referencia'));
    }

    public function actualizar(Request $request, ReporteGenerado $reporte): RedirectResponse
    {
        $filtros = $this->validarFiltros($request);
        $rango = $this->construirRango($filtros);

        if (! $this->tablasFinancierasDisponibles()) {
            return back()->with('error', 'No se puede actualizar el reporte hasta aplicar las migraciones pendientes.');
        }

        $reporte->update([
            'tipo_reporte' => $rango['periodo'],
            'fecha_desde' => $rango['desde']->toDateString(),
            'fecha_hasta' => $rango['hasta']->toDateString(),
            'datos' => $this->datosReporte($rango),
        ]);

        return redirect()->route('reports.show', $reporte)->with('success', 'El reporte se actualizó correctamente.');
    }

    public function eliminar(ReporteGenerado $reporte): RedirectResponse
    {
        $reporte->delete();

        return redirect()->route('reports.index')->with('success', 'El reporte se eliminó correctamente.');
    }

    private function tablasFinancierasDisponibles(): bool
    {
        return Schema::hasTable('presupuestos_mensuales')
            && Schema::hasTable('historial_presupuestos')
            && Schema::hasTable('egresos_mensuales')
            && Schema::hasColumn('egresos_mensuales', 'id_producto')
            && Schema::hasColumn('egresos_mensuales', 'id_proveedor')
            && Schema::hasTable('mermas_productos')
            && Schema::hasTable('reportes_generados');
    }

    private function datosReporte(array $rango): array
    {
        $desde = $rango['desde']->toDateString();
        $hasta = $rango['hasta']->toDateString();
        $facturas = Facturas::query()
            ->where('estado', 'pagada')
            ->whereBetween('fecha', [$desde, $hasta])
            ->orderBy('fecha')
            ->get(['id_factura', 'numero', 'cliente_nombre', 'fecha', 'total']);
        $egresos = EgresoMensual::query()
            ->with(['producto:id,nombre', 'proveedor:id_proveedor,razon_social'])
            ->whereBetween('fecha', [$desde, $hasta])
            ->orderBy('fecha')
            ->get();
        $mermas = MermaProducto::query()
            ->with('producto')
            ->whereBetween('fecha', [$desde, $hasta])
            ->orderBy('fecha')
            ->get();

        $ingresos = (float) $facturas->sum('total');
        $inversiones = (float) $egresos->where('tipo', 'inversion_producto')->sum('monto');
        $otrosGastos = (float) $egresos->where('tipo', 'gasto')->sum('monto');
        $perdidaProductos = (float) $mermas->sum('valor_perdida');
        $presupuesto = $rango['periodo'] === 'mensual'
            ? PresupuestoMensual::query()->whereDate('mes', $rango['desde']->copy()->startOfMonth()->toDateString())->first()
            : null;

        return [
            'etiqueta' => $rango['etiqueta'],
            'resumen' => [
                'presupuesto_inicial' => $presupuesto?->presupuesto_inicial,
                'ingresos' => $ingresos,
                'inversiones' => $inversiones,
                'otros_gastos' => $otrosGastos,
                'perdida_productos' => $perdidaProductos,
                'resultado' => $ingresos - $inversiones - $otrosGastos - $perdidaProductos,
                'presupuesto_final' => $presupuesto
                    ? (float) $presupuesto->presupuesto_inicial + $ingresos - $inversiones - $otrosGastos
                    : null,
            ],
            'facturas' => $facturas->map(fn ($factura) => [
                'numero' => $factura->numero,
                'cliente' => $factura->cliente_nombre,
                'fecha' => $factura->fecha->format('Y-m-d'),
                'total' => (float) $factura->total,
            ])->values()->all(),
            'movimientos' => $egresos->map(fn (EgresoMensual $egreso) => [
                'tipo' => $egreso->tipo,
                'producto' => $egreso->producto?->nombre,
                'proveedor' => $egreso->proveedor?->razon_social,
                'descripcion' => $egreso->descripcion,
                'fecha' => $egreso->fecha->format('Y-m-d'),
                'monto' => (float) $egreso->monto,
            ])->values()->all(),
            'mermas' => $mermas->map(fn (MermaProducto $merma) => [
                'producto' => $merma->producto?->nombre ?? 'Producto eliminado',
                'cantidad' => (float) $merma->cantidad,
                'motivo' => $merma->motivo,
                'fecha' => $merma->fecha->format('Y-m-d'),
                'valor_perdida' => (float) $merma->valor_perdida,
            ])->values()->all(),
        ];
    }

    public function descargar(Request $request): StreamedResponse
    {
        $filtros = $this->validarFiltros($request);
        $rango = $this->construirRango($filtros);
        $desde = $rango['desde']->toDateString();
        $hasta = $rango['hasta']->toDateString();
        $facturas = Facturas::query()
            ->where('estado', 'pagada')
            ->whereBetween('fecha', [$desde, $hasta])
            ->orderBy('fecha')
            ->get(['numero', 'cliente_nombre', 'fecha', 'total']);
        $ingresos = (float) $facturas->sum('total');
        $tablasFinancierasListas = Schema::hasTable('presupuestos_mensuales')
            && Schema::hasTable('historial_presupuestos')
            && Schema::hasTable('egresos_mensuales')
            && Schema::hasColumn('egresos_mensuales', 'id_producto')
            && Schema::hasColumn('egresos_mensuales', 'id_proveedor')
            && Schema::hasTable('mermas_productos');
        $egresos = $tablasFinancierasListas
            ? EgresoMensual::query()->with(['producto:id,nombre', 'proveedor:id_proveedor,razon_social'])->whereBetween('fecha', [$desde, $hasta])->get()
            : collect();
        $mermas = $tablasFinancierasListas
            ? MermaProducto::query()->whereBetween('fecha', [$desde, $hasta])->get()
            : collect();
        $inversiones = (float) $egresos->where('tipo', 'inversion_producto')->sum('monto');
        $otrosGastos = (float) $egresos->where('tipo', 'gasto')->sum('monto');
        $perdidaProductos = (float) $mermas->sum('valor_perdida');
        $periodoMensual = $rango['periodo'] === 'mensual';
        $presupuesto = $periodoMensual && $tablasFinancierasListas
            ? PresupuestoMensual::query()->whereDate('mes', Carbon::parse($desde)->startOfMonth()->toDateString())->first()
            : null;

        return response()->streamDownload(function () use (
            $facturas,
            $rango,
            $tablasFinancierasListas,
            $ingresos,
            $inversiones,
            $otrosGastos,
            $perdidaProductos,
            $periodoMensual,
            $presupuesto,
            $egresos,
            $mermas
        ): void {
            $archivo = fopen('php://output', 'w');
            fwrite($archivo, "\xEF\xBB\xBF");
            fputcsv($archivo, ['Reporte de rentabilidad', $rango['etiqueta']], ';');
            fputcsv($archivo, ['Desde', $rango['desde']->format('d/m/Y')], ';');
            fputcsv($archivo, ['Hasta', $rango['hasta']->format('d/m/Y')], ';');
            fputcsv($archivo, [], ';');
            fputcsv($archivo, ['Resumen', 'Valor'], ';');
            fputcsv($archivo, ['Ingresos por ventas pagadas', number_format($ingresos, 2, '.', '')], ';');

            if ($tablasFinancierasListas) {
                fputcsv($archivo, ['Inversión en productos', number_format($inversiones, 2, '.', '')], ';');
                fputcsv($archivo, ['Otros gastos', number_format($otrosGastos, 2, '.', '')], ';');
                fputcsv($archivo, ['Pérdida por mermas de productos', number_format($perdidaProductos, 2, '.', '')], ';');
                fputcsv($archivo, ['Ganancia / pérdida', number_format($ingresos - $inversiones - $otrosGastos - $perdidaProductos, 2, '.', '')], ';');
                if ($periodoMensual && $presupuesto) {
                    fputcsv($archivo, ['Presupuesto inicial', number_format((float) $presupuesto->presupuesto_inicial, 2, '.', '')], ';');
                    fputcsv($archivo, ['Presupuesto final', number_format((float) $presupuesto->presupuesto_inicial + $ingresos - $inversiones - $otrosGastos, 2, '.', '')], ';');
                }
            } else {
                fputcsv($archivo, ['Inversiones, gastos y pérdidas', 'Requieren aplicar las migraciones pendientes'], ';');
            }

            fputcsv($archivo, [], ';');
            fputcsv($archivo, ['Inversiones y gastos', 'Producto', 'Proveedor', 'Detalle', 'Fecha', 'Monto'], ';');
            foreach ($egresos as $egreso) {
                fputcsv($archivo, [
                    $egreso->tipo === 'inversion_producto' ? 'Inversión en productos' : 'Otro gasto',
                    $egreso->producto?->nombre ?? '',
                    $egreso->proveedor?->razon_social ?? '',
                    $egreso->descripcion,
                    $egreso->fecha->format('d/m/Y'),
                    number_format((float) $egreso->monto, 2, '.', ''),
                ], ';');
            }
            fputcsv($archivo, [], ';');
            fputcsv($archivo, ['Mermas de productos', 'Cantidad', 'Motivo', 'Fecha', 'Valor perdido'], ';');
            foreach ($mermas as $merma) {
                fputcsv($archivo, [
                    $merma->producto?->nombre ?? 'Producto eliminado',
                    number_format((float) $merma->cantidad, 2, '.', ''),
                    $merma->motivo,
                    $merma->fecha->format('d/m/Y'),
                    number_format((float) $merma->valor_perdida, 2, '.', ''),
                ], ';');
            }
            fputcsv($archivo, [], ';');
            fputcsv($archivo, ['Facturas pagadas', 'Cliente', 'Fecha', 'Total'], ';');
            foreach ($facturas as $factura) {
                fputcsv($archivo, [$factura->numero, $factura->cliente_nombre, $factura->fecha, number_format((float) $factura->total, 2, '.', '')], ';');
            }
            fclose($archivo);
        }, "reporte-{$rango['periodo']}-{$desde}-{$hasta}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function guardarPresupuesto(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'mes' => ['required', 'date_format:Y-m'],
            'presupuesto_inicial' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
        ]);
        $mes = Carbon::createFromFormat('Y-m', $datos['mes'])->startOfMonth();

        $presupuesto = PresupuestoMensual::query()->whereDate('mes', $mes->toDateString())->first();
        $nuevoPresupuesto = (float) $datos['presupuesto_inicial'];

        if ($presupuesto === null || round((float) $presupuesto->presupuesto_inicial, 2) !== round($nuevoPresupuesto, 2)) {
            CambioPresupuesto::query()->create([
                'mes' => $mes->toDateString(),
                'presupuesto_anterior' => $presupuesto?->presupuesto_inicial,
                'presupuesto_nuevo' => $nuevoPresupuesto,
                'id_usuario' => $request->user()->getAuthIdentifier(),
            ]);
        }

        PresupuestoMensual::query()->updateOrCreate(
            ['mes' => $mes->toDateString()],
            ['presupuesto_inicial' => $nuevoPresupuesto]
        );

        return redirect()->route('reports.index', [
            'periodo' => 'mensual',
            'referencia' => $datos['mes'] . '-01',
        ])
            ->with('success', 'El presupuesto inicial del mes se guardó correctamente.');
    }

    public function guardarEgreso(Request $request): RedirectResponse
    {
        $filtros = $this->validarFiltros($request);
        $datos = $request->validate([
            'fecha' => ['required', 'date'],
            'tipo' => ['required', 'in:inversion_producto,gasto'],
            'descripcion' => ['nullable', 'required_if:tipo,gasto', 'string', 'max:180'],
            'id_producto' => ['nullable', 'required_if:tipo,inversion_producto', 'integer', 'exists:productos,id'],
            'monto' => ['required', 'numeric', 'gt:0', 'max:999999999999.99'],
        ]);

        $rango = $this->construirRango($filtros);
        $fecha = Carbon::parse($datos['fecha']);
        if ($fecha->lt($rango['desde']) || $fecha->gt($rango['hasta'])) {
            return back()->withErrors(['fecha' => 'La fecha debe pertenecer al período seleccionado.'])->withInput();
        }

        $producto = null;
        if ($datos['tipo'] === 'inversion_producto') {
            $producto = Producto::with('proveedor')->findOrFail($datos['id_producto']);
            if (! $producto->proveedor) {
                return back()->withErrors([
                    'id_producto' => 'Asigna un proveedor a este producto antes de registrar la inversión.',
                ])->withInput();
            }
        }

        EgresoMensual::query()->create([
            'fecha' => $datos['fecha'],
            'tipo' => $datos['tipo'],
            'descripcion' => filled($datos['descripcion'] ?? null)
                ? trim($datos['descripcion'])
                : 'Compra de producto',
            'monto' => $datos['monto'],
            'id_producto' => $producto?->getKey(),
            'id_proveedor' => $producto?->id_proveedor,
        ]);

        return redirect()->route('reports.index', $filtros)
            ->with('success', 'El movimiento se registró correctamente.');
    }

    public function eliminarEgreso(Request $request, EgresoMensual $egreso): RedirectResponse
    {
        $filtros = $this->validarFiltros($request);
        $egreso->delete();

        return redirect()->route('reports.index', $filtros)
            ->with('success', 'El movimiento se eliminó correctamente.');
    }

    public function guardarMerma(Request $request): RedirectResponse
    {
        if (! Schema::hasTable('mermas_productos')) {
            return redirect()->route('reports.index')->with('error', 'Aplica primero las migraciones de SofStock para registrar productos dañados.');
        }

        $datos = $request->validate([
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'id_producto' => ['required', 'integer', 'exists:productos,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'motivo' => ['required', 'string', 'max:180'],
        ]);

        DB::transaction(function () use ($datos): void {
            $producto = Producto::query()->whereKey($datos['id_producto'])->lockForUpdate()->firstOrFail();
            $stockActual = (float) $producto->getRawOriginal('stock_actual');
            $cantidad = (float) $datos['cantidad'];

            if ($cantidad > $stockActual) {
                throw ValidationException::withMessages([
                    'cantidad' => "La cantidad supera el stock disponible ({$stockActual}).",
                ]);
            }

            $valorPerdida = round((float) $producto->precio_base * $cantidad, 2);
            if ($valorPerdida > 999999999999.99) {
                throw ValidationException::withMessages([
                    'cantidad' => 'El valor calculado de la pérdida supera el máximo permitido.',
                ]);
            }

            $producto->decrement('stock_actual', $cantidad);
            MermaProducto::query()->create([
                'id_producto' => $producto->getKey(),
                'fecha' => $datos['fecha'],
                'cantidad' => $cantidad,
                'valor_perdida' => $valorPerdida,
                'motivo' => $datos['motivo'],
            ]);
        });

        return redirect()->route('reports.index')
            ->with('success', 'La merma se registró y se descontó del stock.');
    }

    private function validarFiltros(Request $request): array
    {
        return $request->validate([
            'periodo' => ['nullable', 'in:diario,semanal,mensual,anual,personalizado'],
            'referencia' => ['nullable', 'required_if:periodo,diario,semanal', 'date'],
            'mes' => ['nullable', 'required_if:periodo,mensual', 'date_format:Y-m'],
            'anio' => ['nullable', 'required_if:periodo,anual', 'integer', 'digits:4', 'min:2000', 'max:' . now()->year],
            'fecha_desde' => ['nullable', 'required_if:periodo,personalizado', 'date'],
            'fecha_hasta' => ['nullable', 'required_if:periodo,personalizado', 'date', 'after_or_equal:fecha_desde'],
        ]);
    }

    private function construirRango(array $filtros): array
    {
        $tipo = $filtros['periodo'] ?? 'mensual';
        $referencia = Carbon::parse($filtros['referencia'] ?? now()->toDateString());

        [$desde, $hasta, $etiqueta] = match ($tipo) {
            'diario' => [$referencia->copy()->startOfDay(), $referencia->copy()->endOfDay(), 'Diario'],
            'semanal' => [$referencia->copy()->startOfWeek(Carbon::MONDAY), $referencia->copy()->endOfWeek(Carbon::SUNDAY), 'Semanal'],
            'mensual' => [Carbon::createFromFormat('Y-m', $filtros['mes'] ?? now()->format('Y-m'))->startOfMonth(), Carbon::createFromFormat('Y-m', $filtros['mes'] ?? now()->format('Y-m'))->endOfMonth(), 'Mensual'],
            'anual' => [Carbon::createFromDate((int) ($filtros['anio'] ?? now()->year), 1, 1)->startOfDay(), Carbon::createFromDate((int) ($filtros['anio'] ?? now()->year), 12, 31)->endOfDay(), 'Anual'],
            'personalizado' => [Carbon::parse($filtros['fecha_desde'])->startOfDay(), Carbon::parse($filtros['fecha_hasta'])->endOfDay(), 'Personalizado'],
            default => [$referencia->copy()->startOfDay(), $referencia->copy()->endOfDay(), 'Diario'],
        };

        return [
            'desde' => $desde,
            'hasta' => $hasta,
            'periodo' => $tipo,
            'referencia' => $referencia->toDateString(),
            'etiqueta' => $etiqueta,
        ];
    }
}
