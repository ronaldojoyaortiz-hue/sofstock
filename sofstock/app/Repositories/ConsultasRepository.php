<?php

namespace App\Repositories;

use App\Models\Acta;
use App\Models\Auditoria;
use App\Models\CambioPresupuesto;
use App\Models\Categoria;
use App\Models\Contrato;
use App\Models\EgresoMensual;
use App\Models\Facturas;
use App\Models\Inmobiliario;
use App\Models\MermaProducto;
use App\Models\PresupuestoMensual;
use App\Models\Producto;
use App\Models\Proveedores;
use App\Models\ReporteGenerado;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;

class ConsultasRepository
{
    public function listarProductos(array $filtros): LengthAwarePaginator
    {
        $consulta = Producto::query()
            ->select([
                'id',
                'nombre',
                'id_categoria',
                'id_proveedor',
                'stock_actual',
                'stock_minimo',
                'precio_base',
                'activo',
                'fecha_caducidad',
            ])
            ->with([
                'categoria:id_categoria,nombre',
                'proveedor:id_proveedor,razon_social',
            ]);

        if (! empty($filtros['q'])) {
            $consulta->where('nombre', 'like', '%'.$filtros['q'].'%');
        }

        if (! empty($filtros['categoria'])) {
            $consulta->where('id_categoria', $filtros['categoria']);
        }

        if (! empty($filtros['proveedor'])) {
            $consulta->where('id_proveedor', $filtros['proveedor']);
        }

        if (isset($filtros['stock_min'])) {
            $consulta->where('stock_actual', '>=', $filtros['stock_min']);
        }

        if (isset($filtros['stock_max'])) {
            $consulta->where('stock_actual', '<=', $filtros['stock_max']);
        }

        if (($filtros['existencias'] ?? null) === 'bajo') {
            $consulta->where('stock_minimo', '>', 0)
                ->whereColumn('stock_actual', '<=', 'stock_minimo');
        } elseif (($filtros['existencias'] ?? null) === 'agotado') {
            $consulta->where('stock_actual', '<=', 0);
        }

        if (! empty($filtros['fecha_caducidad'])) {
            $consulta->whereDate('fecha_caducidad', $filtros['fecha_caducidad']);
        }

        return $consulta->orderBy('nombre')->paginate(15)->withQueryString();
    }

    public function detalleProducto(int $id): Producto
    {
        return Producto::query()
            ->with(['categoria', 'proveedor'])
            ->findOrFail($id);
    }

    public function listarCategorias(array $filtros): LengthAwarePaginator
    {
        $consulta = Categoria::query()
            ->select(['id_categoria', 'nombre', 'descripcion', 'activa'])
            ->withCount('productos');

        if (! empty($filtros['q'])) {
            $consulta->where('nombre', 'like', '%'.$filtros['q'].'%');
        }

        if (($filtros['estado'] ?? null) === 'activa') {
            $consulta->where('activa', true);
        } elseif (($filtros['estado'] ?? null) === 'inactiva') {
            $consulta->where('activa', false);
        }

        return $consulta->orderBy('nombre')->paginate(15)->withQueryString();
    }

    public function detalleCategoria(int $id): Categoria
    {
        return Categoria::query()
            ->withCount('productos')
            ->with([
                'productos:id,nombre,id_categoria,id_proveedor,stock_actual,stock_minimo',
                'productos.proveedor:id_proveedor,razon_social',
            ])
            ->findOrFail($id);
    }

    public function listarFacturas(array $filtros): LengthAwarePaginator
    {
        $consulta = Facturas::query()
            ->select([
                'id_factura',
                'numero',
                'cliente_nombre',
                'cliente_documento',
                'fecha',
                'estado',
                'total',
            ]);

        if (! empty($filtros['fecha'])) {
            $consulta->whereDate('fecha', $filtros['fecha']);
        }

        if (! empty($filtros['estado'])) {
            $consulta->where('estado', $filtros['estado']);
        }

        if (! empty($filtros['documento'])) {
            $consulta->where('cliente_documento', 'like', '%'.$filtros['documento'].'%');
        }

        if (isset($filtros['precio_min'])) {
            $consulta->where('total', '>=', $filtros['precio_min']);
        }

        if (isset($filtros['precio_max'])) {
            $consulta->where('total', '<=', $filtros['precio_max']);
        }

        return $consulta->orderByDesc('fecha')
            ->orderByDesc('id_factura')
            ->paginate(15)
            ->withQueryString();
    }

    public function detalleFactura(int $id): Facturas
    {
        return Facturas::query()
            ->with(['detalles.producto:id,nombre'])
            ->findOrFail($id);
    }

    public function listarProveedores(array $filtros): LengthAwarePaginator
    {
        $consulta = Proveedores::query()
            ->select([
                'id_proveedor',
                'razon_social',
                'nit',
                'contacto_nombre',
                'correo',
                'telefono',
                'direccion',
            ]);

        if (! empty($filtros['razon_social'])) {
            $consulta->where(function ($query) use ($filtros): void {
                $query->where('razon_social', 'like', '%'.$filtros['razon_social'].'%')
                    ->orWhere('contacto_nombre', 'like', '%'.$filtros['razon_social'].'%');
            });
        }

        foreach (['nit', 'telefono', 'correo'] as $campo) {
            if (! empty($filtros[$campo])) {
                $consulta->where($campo, 'like', '%'.$filtros[$campo].'%');
            }
        }

        return $consulta->orderBy('razon_social')->paginate(15)->withQueryString();
    }

    public function detalleProveedor(int $id): Proveedores
    {
        return Proveedores::query()->findOrFail($id);
    }

    public function listarUsuarios(array $filtros): LengthAwarePaginator
    {
        $consulta = Usuario::query()
            ->select(['id', 'nombre', 'apellido', 'documento', 'correo', 'id_rol', 'activo'])
            ->with('rol:id_rol,nombre_rol');

        if (! empty($filtros['q'])) {
            $termino = '%'.$filtros['q'].'%';
            $consulta->where(function ($query) use ($termino): void {
                $query->where('nombre', 'like', $termino)
                    ->orWhere('apellido', 'like', $termino);
            });
        }

        foreach (['documento', 'correo'] as $campo) {
            if (! empty($filtros[$campo])) {
                $consulta->where($campo, 'like', '%'.$filtros[$campo].'%');
            }
        }

        if (! empty($filtros['rol'])) {
            $consulta->where('id_rol', $filtros['rol']);
        }

        if (($filtros['estado'] ?? null) === 'activo') {
            $consulta->where('activo', true);
        } elseif (($filtros['estado'] ?? null) === 'inactivo') {
            $consulta->where('activo', false);
        }

        return $consulta->orderBy('apellido')->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();
    }

    public function detalleUsuario(int $id): Usuario
    {
        return Usuario::query()
            ->with(['rol:id_rol,nombre_rol', 'roles:id,name,guard_name'])
            ->findOrFail($id);
    }

    public function listarInmuebles(array $filtros): LengthAwarePaginator
    {
        $consulta = Inmobiliario::query()
            ->select([
                'id_inmueble',
                'codigo',
                'tipo_inmueble',
                'direccion',
                'ciudad',
                'area_m2',
                'estado',
            ])
            ->selectRaw(
                "CASE WHEN estado = ? THEN 'Disponible' ELSE 'No disponible' END AS disponibilidad",
                ['Disponible']
            );

        foreach (['codigo', 'tipo_inmueble', 'ciudad', 'direccion', 'estado'] as $campo) {
            if (! empty($filtros[$campo])) {
                $consulta->where($campo, 'like', '%'.$filtros[$campo].'%');
            }
        }

        if (($filtros['disponibilidad'] ?? null) === 'disponible') {
            $consulta->where('estado', 'Disponible');
        } elseif (($filtros['disponibilidad'] ?? null) === 'no_disponible') {
            $consulta->where('estado', '!=', 'Disponible');
        }

        return $consulta->orderBy('codigo')->paginate(15)->withQueryString();
    }

    public function detalleInmueble(int $id): Inmobiliario
    {
        return Inmobiliario::query()
            ->with('contratos:id_contrato,id_inmueble,numero_contrato,tipo_contrato,estado')
            ->findOrFail($id);
    }

    public function listarContratos(array $filtros): LengthAwarePaginator
    {
        $consulta = Contrato::query()
            ->select([
                'id_contrato',
                'numero_contrato',
                'tipo_contrato',
                'id_inmueble',
                'estado',
            ])
            ->with('inmueble:id_inmueble,codigo,tipo_inmueble');

        foreach (['numero_contrato', 'tipo_contrato', 'estado'] as $campo) {
            if (! empty($filtros[$campo])) {
                $consulta->where($campo, 'like', '%'.$filtros[$campo].'%');
            }
        }

        if (! empty($filtros['inmueble'])) {
            $consulta->where('id_inmueble', $filtros['inmueble']);
        }

        return $consulta->orderByDesc('id_contrato')->paginate(15)->withQueryString();
    }

    public function detalleContrato(int $id): Contrato
    {
        return Contrato::query()
            ->with('inmueble')
            ->findOrFail($id);
    }

    public function listarAuditorias(array $filtros, int $usuarioId): LengthAwarePaginator
    {
        $consulta = $this->auditoriasVisibles($usuarioId)
            ->select([
                'id_auditoria',
                'id_usuario',
                'accion',
                'modulo',
                'descripcion',
                'created_at',
            ])
            ->with('usuario:id,nombre,apellido');

        if (! empty($filtros['accion'])) {
            $consulta->where('accion', $filtros['accion']);
        }

        if (! empty($filtros['usuario'])) {
            $consulta->where('id_usuario', $filtros['usuario']);
        }

        if (! empty($filtros['fecha_desde'])) {
            $consulta->whereDate('created_at', '>=', $filtros['fecha_desde']);
        }

        if (! empty($filtros['fecha_hasta'])) {
            $consulta->whereDate('created_at', '<=', $filtros['fecha_hasta']);
        }

        return $consulta->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();
    }

    public function detalleAuditoria(int $id, int $usuarioId): Auditoria
    {
        return $this->auditoriasVisibles($usuarioId)
            ->with('usuario:id,nombre,apellido')
            ->where('id_auditoria', $id)
            ->firstOrFail();
    }

    public function listarActas(array $filtros): LengthAwarePaginator
    {
        $consulta = Acta::query()
            ->select(['id', 'codigo', 'tipo_acta', 'fecha', 'estado']);

        if (! empty($filtros['numero'])) {
            $consulta->where('codigo', 'like', '%'.$filtros['numero'].'%');
        }

        if (! empty($filtros['tipo_acta'])) {
            $consulta->where('tipo_acta', $filtros['tipo_acta']);
        }

        if (! empty($filtros['fecha'])) {
            $consulta->whereDate('fecha', $filtros['fecha']);
        }

        if (! empty($filtros['estado'])) {
            $consulta->where('estado', $filtros['estado']);
        }

        return $consulta->orderByDesc('fecha')->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();
    }

    public function detalleActa(int $id): Acta
    {
        return Acta::query()
            ->with([
                'usuario:id,nombre,apellido',
                'rol:id_rol,nombre_rol',
                'inmueble',
                'firmante1Usuario:id,nombre,apellido',
                'firmante2Usuario:id,nombre,apellido',
                'firmante1Rol:id_rol,nombre_rol',
                'firmante2Rol:id_rol,nombre_rol',
            ])
            ->findOrFail($id);
    }

    public function listarReportes(array $filtros): LengthAwarePaginator
    {
        $seccion = $filtros['seccion'] ?? 'resumen';

        if ($seccion === 'ventas') {
            $consulta = Facturas::query()
                ->select(['id_factura', 'numero', 'cliente_nombre', 'fecha', 'estado', 'total'])
                ->where('estado', 'pagada');
            $this->aplicarRango($consulta, 'fecha', $filtros);

            return $consulta->orderByDesc('fecha')->paginate(15)->withQueryString();
        }

        if (in_array($seccion, ['egresos', 'inversiones'], true)) {
            $consulta = EgresoMensual::query()
                ->select(['id', 'fecha', 'tipo', 'descripcion', 'monto', 'id_producto', 'id_proveedor'])
                ->with([
                    'producto:id,nombre',
                    'proveedor:id_proveedor,razon_social',
                ])
                ->where('tipo', $seccion === 'inversiones' ? 'inversion_producto' : 'gasto');
            $this->aplicarRango($consulta, 'fecha', $filtros);

            return $consulta->orderByDesc('fecha')->paginate(15)->withQueryString();
        }

        if ($seccion === 'productos_daniados') {
            $consulta = MermaProducto::query()
                ->select(['id', 'id_producto', 'fecha', 'cantidad', 'valor_perdida', 'motivo'])
                ->with('producto:id,nombre');
            $this->aplicarRango($consulta, 'fecha', $filtros);

            return $consulta->orderByDesc('fecha')->paginate(15)->withQueryString();
        }

        if ($seccion === 'presupuestos') {
            $consulta = PresupuestoMensual::query()
                ->select(['id', 'mes', 'presupuesto_inicial'])
                ->withCount('cambios');
            $this->aplicarRango($consulta, 'mes', $filtros);

            return $consulta->orderByDesc('mes')->paginate(15)->withQueryString();
        }

        $consulta = ReporteGenerado::query()
            ->select([
                'id',
                'tipo_reporte',
                'fecha_desde',
                'fecha_hasta',
                'datos',
                'id_usuario',
                'created_at',
            ]);

        if (! empty($filtros['fecha_desde'])) {
            $consulta->whereDate('fecha_desde', '>=', $filtros['fecha_desde']);
        }

        if (! empty($filtros['fecha_hasta'])) {
            $consulta->whereDate('fecha_hasta', '<=', $filtros['fecha_hasta']);
        }

        return $consulta
            ->with('usuario:id,nombre,apellido')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();
    }

    public function resumenReportes(array $filtros): array
    {
        $facturas = Facturas::query()->where('estado', 'pagada');
        $this->aplicarRango($facturas, 'fecha', $filtros);
        $ingresos = (float) $facturas->sum('total');

        $inversiones = EgresoMensual::query()->where('tipo', 'inversion_producto');
        $this->aplicarRango($inversiones, 'fecha', $filtros);
        $totalInversiones = (float) $inversiones->sum('monto');

        $gastos = EgresoMensual::query()->where('tipo', 'gasto');
        $this->aplicarRango($gastos, 'fecha', $filtros);
        $totalGastos = (float) $gastos->sum('monto');

        $mermas = MermaProducto::query();
        $this->aplicarRango($mermas, 'fecha', $filtros);
        $perdidaMermas = (float) $mermas->sum('valor_perdida');

        $cantidadMermas = MermaProducto::query();
        $this->aplicarRango($cantidadMermas, 'fecha', $filtros);

        $perdidas = $totalInversiones + $totalGastos + $perdidaMermas;

        return [
            'ingresos' => $ingresos,
            'inversiones' => $totalInversiones,
            'gastos' => $totalGastos,
            'perdidas_mermas' => $perdidaMermas,
            'perdidas_totales' => $perdidas,
            'ganancia_neta' => $ingresos - $perdidas,
            'registros_danados' => (int) $cantidadMermas->count(),
            'productos_danados' => (int) MermaProducto::query()
                ->when(! empty($filtros['fecha_desde']), fn ($query) => $query->whereDate('fecha', '>=', $filtros['fecha_desde']))
                ->when(! empty($filtros['fecha_hasta']), fn ($query) => $query->whereDate('fecha', '<=', $filtros['fecha_hasta']))
                ->distinct()
                ->count('id_producto'),
        ];
    }

    public function detalleReporte(string $tipo, int $id): object
    {
        return match ($tipo) {
            'egresos', 'inversiones' => EgresoMensual::query()
                ->with([
                    'producto:id,nombre',
                    'proveedor:id_proveedor,razon_social',
                ])
                ->findOrFail($id),
            'productos_daniados' => MermaProducto::query()
                ->with('producto:id,nombre')
                ->findOrFail($id),
            'presupuestos' => $this->detallePresupuesto($id),
            default => ReporteGenerado::query()
                ->with('usuario:id,nombre,apellido')
                ->findOrFail($id),
        };
    }

    public function opcionesFiltros(string $tipo): array
    {
        return match ($tipo) {
            'productos' => [
                'categorias' => Categoria::query()->select(['id_categoria', 'nombre'])->orderBy('nombre')->get(),
                'proveedores' => Proveedores::query()->select(['id_proveedor', 'razon_social'])->orderBy('razon_social')->get(),
            ],
            'usuarios' => [
                'roles' => Rol::query()->select(['id_rol', 'nombre_rol'])->orderBy('nombre_rol')->get(),
            ],
            'contratos' => [
                'inmuebles' => Inmobiliario::query()->select(['id_inmueble', 'codigo'])->orderBy('codigo')->get(),
            ],
            'auditorias' => [
                'usuarios' => Usuario::query()
                    ->select(['id', 'nombre', 'apellido'])
                    ->whereHas('auditorias')
                    ->orderBy('nombre')
                    ->get(),
                'acciones' => Auditoria::query()->select('accion')->distinct()->orderBy('accion')->pluck('accion'),
            ],
            'facturas' => [
                'estados_factura' => Facturas::query()
                    ->select('estado')
                    ->whereNotNull('estado')
                    ->distinct()
                    ->orderBy('estado')
                    ->pluck('estado'),
            ],
            'actas' => [
                'tipos_acta' => Acta::query()
                    ->select('tipo_acta')
                    ->whereNotNull('tipo_acta')
                    ->distinct()
                    ->orderBy('tipo_acta')
                    ->pluck('tipo_acta'),
                'estados_acta' => Acta::query()
                    ->select('estado')
                    ->whereNotNull('estado')
                    ->distinct()
                    ->orderBy('estado')
                    ->pluck('estado'),
            ],
            default => [],
        };
    }

    public function reportesDisponibles(): bool
    {
        return Schema::hasTable('presupuestos_mensuales')
            && Schema::hasTable('historial_presupuestos')
            && Schema::hasTable('egresos_mensuales')
            && Schema::hasColumn('egresos_mensuales', 'id_producto')
            && Schema::hasColumn('egresos_mensuales', 'id_proveedor')
            && Schema::hasTable('mermas_productos')
            && Schema::hasTable('reportes_generados');
    }

    private function auditoriasVisibles(int $usuarioId)
    {
        return Auditoria::query()
            ->whereNotExists(function ($query) use ($usuarioId): void {
                $query->selectRaw('1')
                    ->from('auditoria_usuario_oculta')
                    ->whereColumn('auditoria_usuario_oculta.id_auditoria', 'auditorias.id_auditoria')
                    ->where('auditoria_usuario_oculta.id_usuario', $usuarioId);
            });
    }

    private function aplicarRango($consulta, string $campo, array $filtros): void
    {
        if (! empty($filtros['fecha_desde'])) {
            $consulta->whereDate($campo, '>=', $filtros['fecha_desde']);
        }

        if (! empty($filtros['fecha_hasta'])) {
            $consulta->whereDate($campo, '<=', $filtros['fecha_hasta']);
        }
    }

    private function detallePresupuesto(int $id): PresupuestoMensual
    {
        $presupuesto = PresupuestoMensual::query()->findOrFail($id);
        $cambios = CambioPresupuesto::query()
            ->with('usuario:id,nombre,apellido')
            ->whereDate('mes', $presupuesto->getRawOriginal('mes'))
            ->orderByDesc('created_at')
            ->get();

        return $presupuesto->setRelation('cambios', $cambios);
    }
}
