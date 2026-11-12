<?php

namespace App\Services;

use App\Models\Acta;
use App\Models\Auditoria;
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
use App\Models\Usuario;
use App\Repositories\ConsultasRepository;
use DateTimeInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class ConsultasSistemaService
{
    private const MODULOS = [
        'productos' => [
            'permiso' => 'productos',
            'titulo' => 'Inventario - Productos',
            'descripcion' => 'Busca productos por nombre, categoría, proveedor y existencias.',
            'icono' => 'box',
        ],
        'categorias' => [
            'permiso' => 'categorias',
            'titulo' => 'Categorías',
            'descripcion' => 'Consulta categorías y los productos que pertenecen a cada una.',
            'icono' => 'tag',
        ],
        'facturas' => [
            'permiso' => 'facturas',
            'titulo' => 'Facturas',
            'descripcion' => 'Localiza facturas y revisa sus productos y valores.',
            'icono' => 'chart',
        ],
        'reportes' => [
            'permiso' => 'reportes',
            'titulo' => 'Reportes financieros',
            'descripcion' => 'Consulta ingresos, resultado neto, gastos, mermas y presupuestos.',
            'icono' => 'chart',
        ],
        'proveedores' => [
            'permiso' => 'proveedores',
            'titulo' => 'Proveedores',
            'descripcion' => 'Busca proveedores por razón social, NIT, teléfono o correo.',
            'icono' => 'users',
        ],
        'usuarios' => [
            'permiso' => 'usuarios',
            'titulo' => 'Usuarios',
            'descripcion' => 'Consulta usuarios por nombre, documento, correo, rol o estado.',
            'icono' => 'users',
        ],
        'inmuebles' => [
            'permiso' => 'inmuebles',
            'titulo' => 'Inmuebles',
            'descripcion' => 'Busca inmuebles por código, tipo, ubicación y estado.',
            'icono' => 'home',
        ],
        'contratos' => [
            'permiso' => 'contratos',
            'titulo' => 'Contratos',
            'descripcion' => 'Consulta contratos por número, tipo, inmueble y estado.',
            'icono' => 'history',
        ],
        'auditorias' => [
            'permiso' => 'auditorias',
            'titulo' => 'Auditorías',
            'descripcion' => 'Consulta actividades registradas y su usuario responsable.',
            'icono' => 'history',
        ],
        'actas' => [
            'permiso' => 'actas',
            'titulo' => 'Actas',
            'descripcion' => 'Busca actas por número, tipo, fecha y estado.',
            'icono' => 'history',
        ],
    ];

    private const CAMPOS_SENSIBLES_USUARIO = [
        'contrasena',
        'contrasena_cifrada',
        'password',
        'password_confirmation',
        'remember_token',
        'pregunta_seguridad',
        'respuesta_seguridad',
    ];

    private const CLAVES_SENSIBLES_AUDITORIA = [
        'password',
        'password_confirmation',
        'password_hash',
        'contrasena',
        'contrasena_cifrada',
        'pregunta_seguridad',
        'respuesta_seguridad',
        'remember_token',
        'api_token',
        'access_token',
        'refresh_token',
        'token',
    ];

    public function __construct(private ConsultasRepository $consultasRepository) {}

    public function modulosDisponibles(Usuario $usuario): array
    {
        return collect(self::MODULOS)
            ->filter(fn (array $modulo): bool => $this->puedeConsultar(
                $usuario,
                $modulo['permiso']
            ))
            ->map(fn (array $modulo, string $tipo): array => [
                ...$modulo,
                'tipo' => $tipo,
                'ruta' => route("consultas.{$tipo}.index"),
            ])
            ->values()
            ->all();
    }

    public function reglasFiltros(string $tipo): array
    {
        $this->validarTipo($tipo);

        return match ($tipo) {
            'productos' => [
                'q' => ['nullable', 'string', 'max:150'],
                'categoria' => ['nullable', 'integer', 'exists:categorias,id_categoria'],
                'proveedor' => ['nullable', 'integer', 'exists:proveedores,id_proveedor'],
                'stock_min' => ['nullable', 'numeric', 'min:0'],
                'stock_max' => ['nullable', 'numeric', 'min:0'],
                'existencias' => ['nullable', 'in:bajo,agotado'],
                'fecha_caducidad' => ['nullable', 'date'],
            ],
            'categorias' => [
                'q' => ['nullable', 'string', 'max:100'],
                'estado' => ['nullable', 'in:activa,inactiva'],
            ],
            'facturas' => [
                'fecha' => ['nullable', 'date'],
                'estado' => ['nullable', 'string', 'max:30'],
                'documento' => ['nullable', 'string', 'max:30'],
                'precio_min' => ['nullable', 'numeric', 'min:0'],
                'precio_max' => ['nullable', 'numeric', 'min:0'],
            ],
            'reportes' => [
                'seccion' => ['nullable', 'in:resumen,ventas,egresos,inversiones,productos_daniados,presupuestos'],
                'fecha_desde' => ['nullable', 'date'],
                'fecha_hasta' => ['nullable', 'date'],
            ],
            'proveedores' => [
                'razon_social' => ['nullable', 'string', 'max:150'],
                'nit' => ['nullable', 'string', 'max:30'],
                'telefono' => ['nullable', 'string', 'max:30'],
                'correo' => ['nullable', 'email', 'max:150'],
            ],
            'usuarios' => [
                'q' => ['nullable', 'string', 'max:100'],
                'documento' => ['nullable', 'string', 'max:30'],
                'correo' => ['nullable', 'email', 'max:150'],
                'rol' => ['nullable', 'integer', 'exists:roles,id_rol'],
                'estado' => ['nullable', 'in:activo,inactivo'],
            ],
            'inmuebles' => [
                'codigo' => ['nullable', 'string', 'max:30'],
                'tipo_inmueble' => ['nullable', 'string', 'max:50'],
                'ciudad' => ['nullable', 'string', 'max:100'],
                'direccion' => ['nullable', 'string', 'max:255'],
                'estado' => ['nullable', 'string', 'max:30'],
                'disponibilidad' => ['nullable', 'in:disponible,no_disponible'],
            ],
            'contratos' => [
                'numero_contrato' => ['nullable', 'string', 'max:50'],
                'tipo_contrato' => ['nullable', 'string', 'max:50'],
                'inmueble' => ['nullable', 'integer', 'exists:inmuebles,id_inmueble'],
                'estado' => ['nullable', 'string', 'max:30'],
            ],
            'auditorias' => [
                'accion' => ['nullable', 'string', 'max:50'],
                'usuario' => ['nullable', 'integer', 'exists:usuarios,id'],
                'fecha_desde' => ['nullable', 'date'],
                'fecha_hasta' => ['nullable', 'date'],
            ],
            'actas' => [
                'numero' => ['nullable', 'string', 'max:50'],
                'tipo_acta' => ['nullable', 'string', 'max:50'],
                'fecha' => ['nullable', 'date'],
                'estado' => ['nullable', 'string', 'max:50'],
            ],
        };
    }

    public function definicionFiltros(string $tipo): array
    {
        $this->validarTipo($tipo);

        return match ($tipo) {
            'productos' => [
                $this->texto('q', 'Nombre del producto'),
                $this->select('categoria', 'Categoría', 'categorias', 'id_categoria', 'nombre'),
                $this->select('proveedor', 'Proveedor', 'proveedores', 'id_proveedor', 'razon_social'),
                $this->numero('stock_min', 'Existencias desde'),
                $this->numero('stock_max', 'Existencias hasta'),
                $this->selectValores('existencias', 'Disponibilidad de existencias', [
                    'bajo' => 'Stock bajo',
                    'agotado' => 'Agotado',
                ]),
                $this->fecha('fecha_caducidad', 'Fecha de caducidad'),
            ],
            'categorias' => [
                $this->texto('q', 'Nombre de la categoría'),
                $this->selectValores('estado', 'Estado', [
                    'activa' => 'Activa',
                    'inactiva' => 'Inactiva',
                ]),
            ],
            'facturas' => [
                $this->fecha('fecha', 'Fecha'),
                $this->select('estado', 'Estado', 'estados_factura'),
                $this->texto('documento', 'Documento del cliente'),
                $this->numero('precio_min', 'Precio final desde'),
                $this->numero('precio_max', 'Precio final hasta'),
            ],
            'reportes' => [
                $this->selectValores('seccion', 'Consulta', [
                    'resumen' => 'Resumen financiero y reportes guardados',
                    'ventas' => 'Ventas pagadas',
                    'egresos' => 'Egresos',
                    'inversiones' => 'Inversiones en productos',
                    'productos_daniados' => 'Productos dañados',
                    'presupuestos' => 'Presupuestos mensuales',
                ]),
                $this->fecha('fecha_desde', 'Desde'),
                $this->fecha('fecha_hasta', 'Hasta'),
            ],
            'proveedores' => [
                $this->texto('razon_social', 'Razón social o nombre de contacto'),
                $this->texto('nit', 'NIT'),
                $this->texto('telefono', 'Teléfono'),
                $this->texto('correo', 'Correo electrónico'),
            ],
            'usuarios' => [
                $this->texto('q', 'Nombre o apellido'),
                $this->texto('documento', 'Documento'),
                $this->texto('correo', 'Correo electrónico'),
                $this->select('rol', 'Rol', 'roles', 'id_rol', 'nombre_rol'),
                $this->selectValores('estado', 'Estado', [
                    'activo' => 'Activo',
                    'inactivo' => 'Inactivo',
                ]),
            ],
            'inmuebles' => [
                $this->texto('codigo', 'Código'),
                $this->texto('tipo_inmueble', 'Tipo de inmueble'),
                $this->texto('ciudad', 'Ciudad'),
                $this->texto('direccion', 'Dirección'),
                $this->selectValores('disponibilidad', 'Disponibilidad', [
                    'disponible' => 'Disponible',
                    'no_disponible' => 'No disponible',
                ]),
                $this->texto('estado', 'Estado'),
            ],
            'contratos' => [
                $this->texto('numero_contrato', 'Número de contrato'),
                $this->texto('tipo_contrato', 'Tipo de contrato'),
                $this->select('inmueble', 'Inmueble', 'inmuebles', 'id_inmueble', 'codigo'),
                $this->texto('estado', 'Estado'),
            ],
            'auditorias' => [
                $this->select('accion', 'Acción realizada', 'acciones'),
                $this->select('usuario', 'Usuario responsable', 'usuarios', 'id', 'nombre_completo'),
                $this->fecha('fecha_desde', 'Desde'),
                $this->fecha('fecha_hasta', 'Hasta'),
            ],
            'actas' => [
                $this->texto('numero', 'Número de acta'),
                $this->select('tipo_acta', 'Tipo de acta', 'tipos_acta'),
                $this->fecha('fecha', 'Fecha'),
                $this->select('estado', 'Estado', 'estados_acta'),
            ],
        };
    }

    public function opcionesFiltros(string $tipo): array
    {
        $this->validarTipo($tipo);

        $opciones = $this->consultasRepository->opcionesFiltros($tipo);
        $opciones['acciones'] = $opciones['acciones'] ?? collect();

        if (isset($opciones['usuarios'])) {
            $opciones['usuarios'] = $opciones['usuarios']
                ->map(fn (Usuario $usuario): object => (object) [
                    'id' => $usuario->id,
                    'nombre_completo' => trim($usuario->nombre.' '.$usuario->apellido),
                ]);
        }

        return $opciones;
    }

    public function listar(string $tipo, array $filtros, int $usuarioId): LengthAwarePaginator
    {
        $this->validarTipo($tipo);

        return match ($tipo) {
            'productos' => $this->consultasRepository->listarProductos($filtros),
            'categorias' => $this->consultasRepository->listarCategorias($filtros),
            'facturas' => $this->consultasRepository->listarFacturas($filtros),
            'reportes' => $this->consultasRepository->listarReportes($filtros),
            'proveedores' => $this->consultasRepository->listarProveedores($filtros),
            'usuarios' => $this->consultasRepository->listarUsuarios($filtros),
            'inmuebles' => $this->consultasRepository->listarInmuebles($filtros),
            'contratos' => $this->consultasRepository->listarContratos($filtros),
            'auditorias' => $this->consultasRepository->listarAuditorias($filtros, $usuarioId),
            'actas' => $this->consultasRepository->listarActas($filtros),
        };
    }

    public function reporteDisponible(): bool
    {
        return $this->consultasRepository->reportesDisponibles();
    }

    public function resumenReportes(array $filtros): array
    {
        return $this->consultasRepository->resumenReportes($filtros);
    }

    public function fila(string $tipo, Model $registro, array $filtros = []): array
    {
        if ($tipo === 'reportes') {
            return $this->filaReporte($registro, $filtros['seccion'] ?? 'resumen');
        }

        $columnas = match ($tipo) {
            'productos' => [
                'Producto' => $registro->nombre,
                'Categoría' => $registro->categoria?->nombre ?? 'Sin categoría',
                'Proveedor' => $registro->proveedor?->razon_social ?? 'Sin proveedor',
                'Existencias' => $this->formatear($registro->stock_actual, 'stock_actual'),
                'Stock mínimo' => $this->formatear($registro->stock_minimo, 'stock_minimo'),
                'Fecha de caducidad' => $this->formatear($registro->fecha_caducidad, 'fecha_caducidad'),
            ],
            'categorias' => [
                'Categoría' => $registro->nombre,
                'Estado' => $registro->activa ? 'Activa' : 'Inactiva',
                'Productos asociados' => (string) $registro->productos_count,
            ],
            'facturas' => [
                'Número' => $registro->numero,
                'Cliente' => $registro->cliente_nombre,
                'Documento' => $registro->cliente_documento,
                'Fecha' => $this->formatear($registro->fecha, 'fecha'),
                'Estado' => $this->formatear($registro->estado, 'estado'),
                'Total final' => $this->formatear($registro->total, 'total'),
            ],
            'proveedores' => [
                'Razón social' => $registro->razon_social,
                'NIT' => $registro->nit,
                'Teléfono' => $registro->telefono,
                'Correo' => $registro->correo,
            ],
            'usuarios' => [
                'Nombre' => trim($registro->nombre.' '.$registro->apellido),
                'Documento' => $registro->documento,
                'Correo' => $registro->correo,
                'Rol' => $registro->rol?->nombre_rol ?? 'Sin rol asignado',
                'Estado' => $registro->activo ? 'Activo' : 'Inactivo',
            ],
            'inmuebles' => [
                'Código' => $registro->codigo,
                'Tipo' => $registro->tipo_inmueble,
                'Ubicación' => trim($registro->direccion.', '.$registro->ciudad, ', '),
                'Disponibilidad' => $registro->disponibilidad,
                'Estado' => $registro->estado,
            ],
            'contratos' => [
                'Número' => $registro->numero_contrato,
                'Tipo' => $registro->tipo_contrato,
                'Inmueble' => $registro->inmueble?->codigo ?? 'Sin inmueble asociado',
                'Estado' => $registro->estado,
            ],
            'auditorias' => [
                'Acción' => $this->formatear($registro->accion, 'accion'),
                'Usuario responsable' => $this->nombreUsuario($registro->usuario),
                'Módulo' => $registro->modulo,
                'Fecha' => $this->formatear($registro->created_at, 'created_at'),
            ],
            'actas' => [
                'Número' => $registro->codigo,
                'Tipo de acta' => $this->formatear($registro->tipo_acta, 'tipo_acta'),
                'Fecha' => $this->formatear($registro->fecha, 'fecha'),
                'Estado' => $this->formatear($registro->estado, 'estado'),
            ],
        };

        return [
            'campos' => collect($columnas)
                ->map(fn ($valor): string => $this->formatear($valor))
                ->all(),
            'url' => route("consultas.{$tipo}.detalle", $registro->getKey()),
        ];
    }

    public function columnas(string $tipo, array $filtros = []): array
    {
        if ($tipo === 'reportes') {
            return $this->columnasReporte($filtros['seccion'] ?? 'resumen');
        }

        return match ($tipo) {
            'productos' => ['Producto', 'Categoría', 'Proveedor', 'Existencias', 'Stock mínimo', 'Fecha de caducidad'],
            'categorias' => ['Categoría', 'Estado', 'Productos asociados'],
            'facturas' => ['Número', 'Cliente', 'Documento', 'Fecha', 'Estado', 'Total final'],
            'proveedores' => ['Razón social', 'NIT', 'Teléfono', 'Correo'],
            'usuarios' => ['Nombre', 'Documento', 'Correo', 'Rol', 'Estado'],
            'inmuebles' => ['Código', 'Tipo', 'Ubicación', 'Disponibilidad', 'Estado'],
            'contratos' => ['Número', 'Tipo', 'Inmueble', 'Estado'],
            'auditorias' => ['Acción', 'Usuario responsable', 'Módulo', 'Fecha'],
            'actas' => ['Número', 'Tipo de acta', 'Fecha', 'Estado'],
            default => [],
        };
    }

    public function detalle(string $tipo, int $id, int $usuarioId): array
    {
        $this->validarTipo($tipo);
        $registro = match ($tipo) {
            'productos' => $this->consultasRepository->detalleProducto($id),
            'categorias' => $this->consultasRepository->detalleCategoria($id),
            'facturas' => $this->consultasRepository->detalleFactura($id),
            'reportes' => $this->consultasRepository->detalleReporte('resumen', $id),
            'proveedores' => $this->consultasRepository->detalleProveedor($id),
            'usuarios' => $this->consultasRepository->detalleUsuario($id),
            'inmuebles' => $this->consultasRepository->detalleInmueble($id),
            'contratos' => $this->consultasRepository->detalleContrato($id),
            'auditorias' => $this->consultasRepository->detalleAuditoria($id, $usuarioId),
            'actas' => $this->consultasRepository->detalleActa($id),
            default => abort(404),
        };

        return $this->presentarDetalle($tipo, $registro);
    }

    public function detalleReporte(string $subtipo, int $id): array
    {
        $permitidos = ['egresos', 'inversiones', 'productos_daniados', 'presupuestos'];
        abort_unless(in_array($subtipo, $permitidos, true), 404);

        $registro = $this->consultasRepository->detalleReporte($subtipo, $id);

        return $this->presentarDetalle('reportes', $registro);
    }

    public function filas(
        string $tipo,
        LengthAwarePaginator $registros,
        array $filtros = []
    ): LengthAwarePaginator {
        return $registros->through(
            fn (Model $registro): array => $this->fila($tipo, $registro, $filtros)
        );
    }

    private function presentarDetalle(string $tipo, Model $registro): array
    {
        $relacionadosOmitidos = match ($tipo) {
            'productos' => ['id_categoria', 'id_proveedor'],
            'facturas' => [],
            'usuarios' => ['id_rol'],
            'contratos' => ['id_inmueble'],
            'auditorias' => ['id_usuario'],
            'actas' => ['usuario_id', 'rol_id', 'inmueble_id', 'productos', 'firmantes'],
            default => [],
        };

        if ($registro instanceof EgresoMensual) {
            $relacionadosOmitidos = ['id_producto', 'id_proveedor'];
        } elseif ($registro instanceof MermaProducto) {
            $relacionadosOmitidos = ['id_producto'];
        } elseif ($registro instanceof ReporteGenerado) {
            $relacionadosOmitidos = ['datos', 'id_usuario'];
        }

        $campos = $this->camposModelo($registro, $relacionadosOmitidos);
        $secciones = [[
            'titulo' => 'Información registrada',
            'campos' => $campos,
        ]];

        if ($registro instanceof Producto) {
            $secciones[] = $this->seccionCampos('Relaciones', [
                'Categoría' => $registro->categoria?->nombre ?? 'Sin categoría',
                'Proveedor' => $registro->proveedor?->razon_social ?? 'Sin proveedor',
                'Estado calculado según existencias' => $registro->estado,
            ]);
        } elseif ($registro instanceof Categoria) {
            $secciones[] = $this->seccionTabla(
                'Productos de esta categoría',
                ['Producto', 'Proveedor', 'Existencias', 'Stock mínimo'],
                $registro->productos->map(fn (Producto $producto): array => [
                    $producto->nombre,
                    $producto->proveedor?->razon_social ?? 'Sin proveedor',
                    $this->formatear($producto->stock_actual, 'stock_actual'),
                    $this->formatear($producto->stock_minimo, 'stock_minimo'),
                ])->all()
            );
        } elseif ($registro instanceof Facturas) {
            $secciones[] = $this->seccionTabla(
                'Productos facturados',
                ['Producto', 'Cantidad', 'Precio unitario', 'Impuesto', 'Descuento', 'Subtotal'],
                $registro->detalles->map(fn ($detalle): array => [
                    $detalle->producto?->nombre ?? $detalle->producto_nombre ?? 'Producto no disponible',
                    $this->formatear($detalle->cantidad, 'cantidad'),
                    $this->formatear($detalle->precio_unitario, 'precio_unitario'),
                    $this->formatear($detalle->porcentaje_impuesto, 'porcentaje_impuesto').'%',
                    $this->formatear($detalle->descuento, 'descuento'),
                    $this->formatear($detalle->subtotal, 'subtotal'),
                ])->all()
            );
        } elseif ($registro instanceof Usuario) {
            $roles = collect([$registro->rol?->nombre_rol])
                ->merge($registro->roles->pluck('name'))
                ->filter()
                ->unique()
                ->values()
                ->all();
            $secciones[] = $this->seccionCampos('Roles asignados', [
                'Roles' => $roles === [] ? 'Sin rol asignado' : implode(', ', $roles),
            ]);
        } elseif ($registro instanceof Inmobiliario) {
            $secciones[] = $this->seccionCampos('Disponibilidad', [
                'Disponibilidad actual' => $registro->estado === 'Disponible'
                    ? 'Disponible'
                    : 'No disponible según el estado actual',
            ]);
            $secciones[] = $this->seccionTabla(
                'Contratos asociados',
                ['Número', 'Tipo', 'Estado'],
                $registro->contratos->map(fn (Contrato $contrato): array => [
                    $contrato->numero_contrato,
                    $contrato->tipo_contrato,
                    $contrato->estado,
                ])->all()
            );
        } elseif ($registro instanceof Contrato) {
            $inmueble = $registro->inmueble;
            $secciones[] = $this->seccionCampos('Inmueble asociado', [
                'Código' => $inmueble?->codigo ?? 'Sin inmueble asociado',
                'Tipo' => $inmueble?->tipo_inmueble ?? 'No registrado',
                'Dirección' => $inmueble?->direccion ?? 'No registrada',
                'Ciudad' => $inmueble?->ciudad ?? 'No registrada',
            ]);
        } elseif ($registro instanceof PresupuestoMensual) {
            $secciones[] = $this->seccionTabla(
                'Cambios de presupuesto',
                ['Fecha del cambio', 'Valor anterior', 'Valor nuevo', 'Usuario responsable'],
                $registro->cambios->map(fn ($cambio): array => [
                    $this->formatear($cambio->created_at, 'created_at'),
                    $this->formatear($cambio->presupuesto_anterior, 'presupuesto_anterior'),
                    $this->formatear($cambio->presupuesto_nuevo, 'presupuesto_nuevo'),
                    $this->nombreUsuario($cambio->usuario),
                ])->all()
            );
        } elseif ($registro instanceof Auditoria) {
            $secciones[] = $this->seccionCampos('Usuario responsable', [
                'Nombre' => $this->nombreUsuario($registro->usuario),
            ]);
            $secciones[] = $this->seccionCampos('Cambios registrados', [
                'Datos anteriores' => $this->formatear($registro->datos_anteriores, 'datos_anteriores'),
                'Datos nuevos' => $this->formatear($registro->datos_nuevos, 'datos_nuevos'),
                'Dirección IP' => $registro->direccion_ip ?: 'No registrada',
                'Navegador o dispositivo' => $registro->user_agent ?: 'No registrado',
            ]);
        } elseif ($registro instanceof Acta) {
            $secciones[] = $this->seccionCampos('Personas y relaciones', [
                'Responsable del registro' => $registro->usuario_nombre ?: $this->nombreUsuario($registro->usuario),
                'Rol al registrar' => $registro->rol_nombre ?: $registro->rol?->nombre_rol ?? 'No registrado',
                'Inmueble' => $registro->inmueble
                    ? $registro->inmueble->codigo.' · '.$registro->inmueble->direccion
                    : 'No asociado',
            ]);
            $secciones[] = $this->seccionTabla(
                'Productos incluidos en el acta',
                ['Producto', 'Cantidad', 'Observaciones'],
                collect($registro->productos ?? [])->map(fn (array $producto): array => [
                    $producto['nombre'] ?? 'Producto no disponible',
                    $this->formatear($producto['cantidad'] ?? null, 'cantidad'),
                    $producto['observaciones'] ?? '—',
                ])->all()
            );
            $secciones[] = $this->seccionTabla(
                'Firmantes',
                ['Nombre', 'Cargo o rol'],
                collect($registro->firmantesParaMostrar())->map(fn (array $firmante): array => [
                    $firmante['nombre'] ?? 'No registrado',
                    $firmante['cargo'] ?? $firmante['rol'] ?? 'No registrado',
                ])->all()
            );
        } elseif ($registro instanceof EgresoMensual) {
            $secciones[] = $this->seccionCampos('Relaciones', [
                'Producto' => $registro->producto?->nombre ?? 'No asociado',
                'Proveedor' => $registro->proveedor?->razon_social ?? 'No asociado',
                'Tipo de movimiento' => $registro->tipo === 'inversion_producto' ? 'Inversión en productos' : 'Gasto',
            ]);
        } elseif ($registro instanceof MermaProducto) {
            $secciones[] = $this->seccionCampos('Producto relacionado', [
                'Nombre' => $registro->producto?->nombre ?? 'Producto no disponible',
            ]);
        } elseif ($registro instanceof ReporteGenerado) {
            $datos = $registro->datos ?? [];
            $resumen = $datos['resumen'] ?? [];
            $secciones[] = $this->seccionCampos('Persona responsable', [
                'Usuario que generó el reporte' => $this->nombreUsuario($registro->usuario),
            ]);
            $secciones[] = $this->seccionCampos('Resumen financiero', [
                'Período' => $datos['etiqueta'] ?? $registro->tipo_reporte,
                'Ingresos por ventas pagadas' => $this->formatear($resumen['ingresos'] ?? null, 'total'),
                'Inversiones en productos' => $this->formatear($resumen['inversiones'] ?? null, 'total'),
                'Otros gastos' => $this->formatear($resumen['otros_gastos'] ?? null, 'total'),
                'Pérdida por productos dañados' => $this->formatear($resumen['perdida_productos'] ?? null, 'total'),
                'Ganancia o pérdida neta' => $this->formatear($resumen['resultado'] ?? null, 'total'),
                'Presupuesto inicial' => $this->formatear($resumen['presupuesto_inicial'] ?? null, 'total'),
                'Presupuesto final' => $this->formatear($resumen['presupuesto_final'] ?? null, 'total'),
            ]);
            $secciones[] = $this->seccionTabla(
                'Ventas pagadas',
                ['Factura', 'Cliente', 'Fecha', 'Total'],
                collect($datos['facturas'] ?? [])->map(fn (array $factura): array => [
                    $factura['numero'] ?? '—',
                    $factura['cliente'] ?? '—',
                    $this->formatear($factura['fecha'] ?? null, 'fecha'),
                    $this->formatear($factura['total'] ?? null, 'total'),
                ])->all()
            );
            $secciones[] = $this->seccionTabla(
                'Inversiones y gastos',
                ['Fecha', 'Tipo', 'Producto', 'Proveedor', 'Detalle', 'Monto'],
                collect($datos['movimientos'] ?? [])->map(fn (array $movimiento): array => [
                    $this->formatear($movimiento['fecha'] ?? null, 'fecha'),
                    $movimiento['tipo'] ?? '—',
                    $movimiento['producto'] ?? '—',
                    $movimiento['proveedor'] ?? '—',
                    $movimiento['descripcion'] ?? '—',
                    $this->formatear($movimiento['monto'] ?? null, 'monto'),
                ])->all()
            );
            $secciones[] = $this->seccionTabla(
                'Productos dañados',
                ['Producto', 'Cantidad', 'Motivo', 'Fecha', 'Pérdida'],
                collect($datos['mermas'] ?? [])->map(fn (array $merma): array => [
                    $merma['producto'] ?? '—',
                    $this->formatear($merma['cantidad'] ?? null, 'cantidad'),
                    $merma['motivo'] ?? '—',
                    $this->formatear($merma['fecha'] ?? null, 'fecha'),
                    $this->formatear($merma['valor_perdida'] ?? null, 'valor_perdida'),
                ])->all()
            );
        }

        if ($registro instanceof Usuario) {
            $campos = $secciones[0]['campos'];
            $secciones[0]['campos'] = array_values(array_filter(
                $campos,
                fn (array $campo): bool => ! in_array($campo['nombre'], self::CAMPOS_SENSIBLES_USUARIO, true)
            ));
        }

        return [
            'titulo' => $this->tituloDetalle($tipo, $registro),
            'secciones' => $secciones,
        ];
    }

    private function camposModelo(Model $registro, array $omitir = []): array
    {
        $campos = [];

        foreach (array_keys($registro->getAttributes()) as $nombre) {
            if (
                in_array($nombre, $omitir, true)
                || ($registro instanceof Usuario && in_array($nombre, self::CAMPOS_SENSIBLES_USUARIO, true))
            ) {
                continue;
            }

            $campos[] = [
                'nombre' => $nombre,
                'etiqueta' => $this->etiqueta($nombre),
                'valor' => $this->formatear($registro->getAttribute($nombre), $nombre),
            ];
        }

        return $campos;
    }

    private function filaReporte(Model $registro, string $seccion): array
    {
        if ($registro instanceof Facturas) {
            return [
                'campos' => [
                    'Factura' => $registro->numero,
                    'Cliente' => $registro->cliente_nombre,
                    'Fecha' => $this->formatear($registro->fecha, 'fecha'),
                    'Estado' => $this->formatear($registro->estado, 'estado'),
                    'Total' => $this->formatear($registro->total, 'total'),
                ],
                'url' => route('consultas.facturas.detalle', $registro->id_factura),
            ];
        }

        if ($registro instanceof EgresoMensual) {
            return [
                'campos' => [
                    'Fecha' => $this->formatear($registro->fecha, 'fecha'),
                    'Concepto' => $registro->descripcion,
                    'Producto' => $registro->producto?->nombre ?? '—',
                    'Proveedor' => $registro->proveedor?->razon_social ?? '—',
                    'Monto' => $this->formatear($registro->monto, 'monto'),
                ],
                'url' => route('consultas.reportes.registro', [$seccion, $registro->id]),
            ];
        }

        if ($registro instanceof MermaProducto) {
            return [
                'campos' => [
                    'Fecha' => $this->formatear($registro->fecha, 'fecha'),
                    'Producto' => $registro->producto?->nombre ?? 'Producto no disponible',
                    'Cantidad' => $this->formatear($registro->cantidad, 'cantidad'),
                    'Motivo' => $registro->motivo,
                    'Pérdida' => $this->formatear($registro->valor_perdida, 'valor_perdida'),
                ],
                'url' => route('consultas.reportes.registro', [$seccion, $registro->id]),
            ];
        }

        if ($registro instanceof PresupuestoMensual) {
            return [
                'campos' => [
                    'Mes' => $this->formatear($registro->mes, 'mes'),
                    'Presupuesto inicial' => $this->formatear($registro->presupuesto_inicial, 'presupuesto_inicial'),
                    'Cambios registrados' => (string) $registro->cambios_count,
                ],
                'url' => route('consultas.reportes.registro', [$seccion, $registro->id]),
            ];
        }

        if ($registro instanceof ReporteGenerado) {
            return [
                'campos' => [
                    'Tipo de reporte' => ucfirst($registro->tipo_reporte),
                    'Período' => $this->formatear($registro->fecha_desde, 'fecha_desde')
                        .' al '.$this->formatear($registro->fecha_hasta, 'fecha_hasta'),
                    'Generado' => $this->formatear($registro->created_at, 'created_at'),
                ],
                'url' => route('consultas.reportes.detalle', $registro->id),
            ];
        }

        return ['campos' => [], 'url' => '#'];
    }

    private function columnasReporte(string $seccion): array
    {
        return match ($seccion) {
            'ventas' => ['Factura', 'Cliente', 'Fecha', 'Estado', 'Total'],
            'egresos', 'inversiones' => ['Fecha', 'Concepto', 'Producto', 'Proveedor', 'Monto'],
            'productos_daniados' => ['Fecha', 'Producto', 'Cantidad', 'Motivo', 'Pérdida'],
            'presupuestos' => ['Mes', 'Presupuesto inicial', 'Cambios registrados'],
            default => ['Tipo de reporte', 'Período', 'Generado'],
        };
    }

    private function tituloDetalle(string $tipo, Model $registro): string
    {
        return match (true) {
            $registro instanceof Producto => 'Producto: '.$registro->nombre,
            $registro instanceof Categoria => 'Categoría: '.$registro->nombre,
            $registro instanceof Facturas => 'Factura: '.($registro->numero ?: $registro->id_factura),
            $registro instanceof Proveedores => 'Proveedor: '.$registro->razon_social,
            $registro instanceof Usuario => 'Usuario: '.trim($registro->nombre.' '.$registro->apellido),
            $registro instanceof Inmobiliario => 'Inmueble: '.$registro->codigo,
            $registro instanceof Contrato => 'Contrato: '.$registro->numero_contrato,
            $registro instanceof Auditoria => 'Actividad de auditoría #'.$registro->id_auditoria,
            $registro instanceof Acta => 'Acta: '.$registro->codigo,
            $registro instanceof EgresoMensual => 'Movimiento financiero: '.$registro->descripcion,
            $registro instanceof MermaProducto => 'Producto dañado: '.($registro->producto?->nombre ?? $registro->id_producto),
            $registro instanceof PresupuestoMensual => 'Presupuesto mensual: '.$this->formatear($registro->mes, 'mes'),
            $registro instanceof ReporteGenerado => 'Reporte guardado #'.$registro->id,
            default => ucfirst($tipo),
        };
    }

    private function formatear(mixed $valor, string $campo = ''): string
    {
        if ($valor === null || $valor === '') {
            return 'No registrado';
        }

        if ($valor instanceof DateTimeInterface) {
            return in_array($campo, ['created_at', 'updated_at', 'fecha_registro', 'fecha_actualizacion'], true)
                ? $valor->format('d/m/Y H:i')
                : $valor->format('d/m/Y');
        }

        if (is_bool($valor)) {
            return $valor ? 'Sí' : 'No';
        }

        if (is_array($valor)) {
            if ($campo === 'archivos') {
                return implode(', ', array_map(
                    fn (array $archivo): string => $archivo['name'] ?? 'Archivo adjunto',
                    $valor
                )) ?: 'Sin archivos';
            }

            if (in_array($campo, ['datos_anteriores', 'datos_nuevos'], true)) {
                $valor = $this->ocultarDatosSensibles($valor);
            }

            return json_encode($valor, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) ?: 'Sin información';
        }

        if (
            is_numeric($valor)
            && in_array($campo, [
                'precio_base',
                'precio_unitario',
                'total',
                'subtotal',
                'impuesto',
                'descuento',
                'monto',
                'valor',
                'valor_perdida',
                'presupuesto_anterior',
                'presupuesto_inicial',
                'presupuesto_final',
            ], true)
        ) {
            return number_format((float) $valor, 2, ',', '.');
        }

        if (is_numeric($valor) && in_array($campo, ['stock_actual', 'stock_minimo', 'cantidad', 'area_m2'], true)) {
            return number_format((float) $valor, 2, ',', '.');
        }

        if ($campo === 'estado' || $campo === 'tipo_acta' || $campo === 'accion') {
            return ucfirst(str_replace('_', ' ', (string) $valor));
        }

        return (string) $valor;
    }

    private function etiqueta(string $campo): string
    {
        return [
            'id' => 'Identificador interno',
            'id_categoria' => 'Identificador de categoría',
            'id_proveedor' => 'Identificador de proveedor',
            'id_rol' => 'Identificador de rol',
            'id_factura' => 'Identificador de factura',
            'id_inmueble' => 'Identificador de inmueble',
            'id_contrato' => 'Identificador de contrato',
            'id_usuario' => 'Identificador de usuario',
            'codigo' => 'Número o código',
            'numero' => 'Número de factura',
            'numero_contrato' => 'Número de contrato',
            'tipo' => 'Tipo',
            'tipo_acta' => 'Tipo de acta',
            'tipo_contrato' => 'Tipo de contrato',
            'tipo_inmueble' => 'Tipo de inmueble',
            'tipo_reporte' => 'Tipo de reporte',
            'nombre' => 'Nombre',
            'apellido' => 'Apellido',
            'nombre_rol' => 'Rol',
            'nombre_empresa' => 'Nombre de la empresa',
            'razon_social' => 'Razón social',
            'contacto_nombre' => 'Nombre de contacto',
            'nit' => 'NIT',
            'correo' => 'Correo electrónico',
            'telefono' => 'Teléfono',
            'direccion' => 'Dirección',
            'ciudad' => 'Ciudad',
            'documento' => 'Documento',
            'cliente_nombre' => 'Nombre del cliente',
            'cliente_documento' => 'Documento del cliente',
            'descripcion' => 'Descripción',
            'marca' => 'Marca',
            'stock_actual' => 'Existencias actuales',
            'stock_minimo' => 'Existencias mínimas',
            'unidad_base' => 'Unidad de medida',
            'precio_base' => 'Precio base',
            'precio_unitario' => 'Precio unitario',
            'cantidad' => 'Cantidad',
            'total' => 'Total final',
            'subtotal' => 'Subtotal',
            'impuesto' => 'Impuesto',
            'porcentaje_impuesto' => 'Porcentaje de impuesto',
            'descuento' => 'Descuento',
            'fecha' => 'Fecha',
            'fecha_inicio' => 'Fecha de inicio',
            'fecha_fin' => 'Fecha de finalización',
            'fecha_desde' => 'Período desde',
            'fecha_hasta' => 'Período hasta',
            'fecha_caducidad' => 'Fecha de vencimiento',
            'fecha_registro' => 'Fecha de registro',
            'fecha_actualizacion' => 'Última actualización',
            'created_at' => 'Fecha de creación',
            'updated_at' => 'Última actualización',
            'estado' => 'Estado',
            'activo' => 'Cuenta activa',
            'activa' => 'Categoría activa',
            'estado_aprobacion' => 'Estado de aprobación',
            'area_m2' => 'Área (m²)',
            'observaciones' => 'Observaciones',
            'hora' => 'Hora',
            'lugar' => 'Lugar',
            'titulo' => 'Título',
            'objetivo' => 'Objetivo',
            'desarrollo' => 'Desarrollo',
            'conclusiones' => 'Conclusiones',
            'usuario_nombre' => 'Responsable del registro',
            'rol_nombre' => 'Rol al registrar',
            'firmante_1_nombre' => 'Primer firmante',
            'firmante_1_cargo' => 'Cargo del primer firmante',
            'firmante_2_nombre' => 'Segundo firmante',
            'firmante_2_cargo' => 'Cargo del segundo firmante',
            'archivos' => 'Archivos adjuntos',
            'productos' => 'Productos incluidos',
            'firmantes' => 'Firmantes',
            'accion' => 'Acción realizada',
            'modulo' => 'Área del sistema',
            'tabla_afectada' => 'Información relacionada',
            'id_registro_afectado' => 'Registro relacionado',
            'datos_anteriores' => 'Información anterior',
            'datos_nuevos' => 'Información nueva',
            'motivo' => 'Motivo',
            'direccion_ip' => 'Dirección de conexión',
            'user_agent' => 'Navegador o dispositivo',
            'fecha_desde' => 'Fecha desde',
            'fecha_hasta' => 'Fecha hasta',
            'mes' => 'Mes',
            'presupuesto_inicial' => 'Presupuesto inicial',
            'monto' => 'Monto',
            'valor' => 'Valor',
            'valor_perdida' => 'Valor de la pérdida',
        ][$campo] ?? ucfirst(str_replace('_', ' ', $campo));
    }

    private function nombreUsuario(?Usuario $usuario): string
    {
        return $usuario
            ? trim($usuario->nombre.' '.$usuario->apellido)
            : 'Usuario no disponible';
    }

    private function seccionCampos(string $titulo, array $campos): array
    {
        return [
            'titulo' => $titulo,
            'campos' => collect($campos)->map(fn ($valor, $etiqueta): array => [
                'nombre' => null,
                'etiqueta' => $etiqueta,
                'valor' => $this->formatear($valor),
            ])->values()->all(),
        ];
    }

    private function seccionTabla(string $titulo, array $columnas, array $filas): array
    {
        return [
            'titulo' => $titulo,
            'columnas' => $columnas,
            'filas' => $filas,
        ];
    }

    private function ocultarDatosSensibles(array $datos): array
    {
        foreach ($datos as $clave => $valor) {
            $claveNormalizada = mb_strtolower((string) $clave);
            $esSensible = in_array($claveNormalizada, self::CLAVES_SENSIBLES_AUDITORIA, true)
                || str_contains($claveNormalizada, 'password')
                || str_contains($claveNormalizada, 'contrasena')
                || str_contains($claveNormalizada, 'token');

            if ($esSensible) {
                unset($datos[$clave]);
            } elseif (is_array($valor)) {
                $datos[$clave] = $this->ocultarDatosSensibles($valor);
            }
        }

        return $datos;
    }

    private function puedeConsultar(Usuario $usuario, string $modulo): bool
    {
        return $usuario->esAdministrador()
            || ($usuario->can("acceder {$modulo}") && $usuario->can("ver {$modulo}"));
    }

    private function validarTipo(string $tipo): void
    {
        abort_unless(isset(self::MODULOS[$tipo]), 404);
    }

    private function texto(string $nombre, string $etiqueta): array
    {
        return compact('nombre', 'etiqueta') + ['tipo' => 'text'];
    }

    private function numero(string $nombre, string $etiqueta): array
    {
        return compact('nombre', 'etiqueta') + ['tipo' => 'number'];
    }

    private function fecha(string $nombre, string $etiqueta): array
    {
        return compact('nombre', 'etiqueta') + ['tipo' => 'date'];
    }

    private function select(string $nombre, string $etiqueta, string $fuente, ?string $valor = null, ?string $texto = null): array
    {
        return compact('nombre', 'etiqueta', 'fuente', 'valor', 'texto') + ['tipo' => 'select'];
    }

    private function selectValores(string $nombre, string $etiqueta, array $valores): array
    {
        return compact('nombre', 'etiqueta', 'valores') + ['tipo' => 'select'];
    }
}
