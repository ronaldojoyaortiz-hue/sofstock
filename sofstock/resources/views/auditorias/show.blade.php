@extends('layouts.app')

@section('title', 'Detalle de auditoría')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">

    {{-- Encabezado --}}
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                Detalle de auditoría
            </h1>

            <p class="mt-1 text-sm text-gray-600">
                Información de la actividad registrada en el sistema.
            </p>
        </div>

        <a href="{{ route('auditorias.index') }}"
           class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 font-medium text-gray-700 transition hover:bg-gray-100">
            Volver al historial
        </a>
    </div>

    {{-- Información general --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

        <h2 class="mb-5 text-lg font-semibold text-gray-800">
            Información general
        </h2>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

            <div>
                <p class="text-sm text-gray-500">Código de auditoría</p>
                <p class="mt-1 font-semibold text-gray-800">
                    #{{ $auditoria->id_auditoria }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Fecha y hora</p>
                <p class="mt-1 font-semibold text-gray-800">
                    {{ $auditoria->created_at?->format('d/m/Y H:i:s') ?? 'Sin fecha' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Usuario responsable</p>
                <p class="mt-1 font-semibold text-gray-800">
                    @if ($auditoria->usuario)
                        {{ $auditoria->usuario->nombre }}
                        {{ $auditoria->usuario->apellido ?? '' }}
                    @else
                        Usuario no disponible
                    @endif
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Acción realizada</p>
                <p class="mt-1 font-semibold text-gray-800">
                    {{ ucfirst(strtolower(str_replace('_', ' ', $auditoria->accion))) }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Módulo</p>
                <p class="mt-1 font-semibold text-gray-800">
                    {{ ucfirst($auditoria->modulo ?? 'No especificado') }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Registro afectado</p>
                <p class="mt-1 font-semibold text-gray-800">
                    @if ($auditoria->id_registro_afectado)
                        Registro #{{ $auditoria->id_registro_afectado }}
                    @else
                        No aplica
                    @endif
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Dirección IP</p>
                <p class="mt-1 font-semibold text-gray-800">
                    {{ $auditoria->direccion_ip ?? 'No disponible' }}
                </p>
            </div>

            <div class="md:col-span-2">
                <p class="text-sm text-gray-500">Descripción de la actividad</p>
                <p class="mt-1 text-gray-800">
                    {{ $auditoria->descripcion ?? 'Sin descripción disponible.' }}
                </p>
            </div>

            <div class="md:col-span-2">
                <p class="text-sm text-gray-500">Motivo</p>
                <p class="mt-1 text-gray-800">
                    {{ $auditoria->motivo ?: 'No se especificó un motivo.' }}
                </p>
            </div>

        </div>
    </div>

    {{-- Cambios realizados, presentados en lenguaje sencillo --}}
    @php
        $etiquetas = [
            'nombre' => 'Nombre',
            'apellido' => 'Apellido',
            'correo' => 'Correo electrónico',
            'id_rol' => 'Rol asignado',
            'activo' => 'Estado de la cuenta',
            'id' => 'Número de usuario',
            'id_usuario' => 'Usuario relacionado',
            'descripcion' => 'Descripción',
            'telefono' => 'Teléfono',
            'direccion' => 'Dirección',
            'documento' => 'Documento',
            'tipo_documento' => 'Tipo de documento',
            'nombre_rol' => 'Nombre del rol',
            'nombre_categoria' => 'Nombre de la categoría',
            'nombre_producto' => 'Nombre del producto',
            'stock_actual' => 'Existencias actuales',
            'stock_minimo' => 'Existencias mínimas',
            'precio_base' => 'Precio base',
            'unidad_base' => 'Unidad de medida',
        ];

        $formatearDatos = function ($datos) use ($etiquetas) {
            if (is_string($datos)) {
                $datos = json_decode($datos, true);
            }

            if (is_object($datos)) {
                $datos = (array) $datos;
            }

            if (!is_array($datos)) {
                return [];
            }

            $resultado = [];

            foreach ($datos as $campo => $valor) {
                // No mostrar contraseñas ni campos técnicos.
                if (
                    in_array(strtolower((string) $campo), [
                        'contrasena',
                        'password',
                        'remember_token',
                        'created_at',
                        'updated_at',
                        'deleted_at',
                        'id',
                        'id_usuario',
                        'id_rol',
                    ], true)
                ) {
                    continue;
                }

                $etiqueta = $etiquetas[$campo] ?? null;

                // Ocultar campos que no tienen una etiqueta comprensible.
                if (!$etiqueta) {
                    continue;
                }

                if ($campo === 'activo') {
                    $valor = filter_var($valor, FILTER_VALIDATE_BOOLEAN)
                        ? 'Activo'
                        : 'Inactivo';
                } elseif ($campo === 'id_rol') {
                    $valor = 'Rol asignado';
                } elseif (is_bool($valor)) {
                    $valor = $valor ? 'Sí' : 'No';
                } elseif (is_array($valor) || is_object($valor)) {
                    $valor = 'Información registrada';
                } elseif ($valor === null || $valor === '') {
                    $valor = 'Sin información';
                }

                $resultado[$etiqueta] = $valor;
            }

            return $resultado;
        };

        $datosAnteriores = $formatearDatos($auditoria->datos_anteriores);
        $datosNuevos = $formatearDatos($auditoria->datos_nuevos);
    @endphp

    @if (count($datosAnteriores) > 0)
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-gray-800">
                Información anterior
            </h2>

            <div class="divide-y divide-gray-100">
                @foreach ($datosAnteriores as $etiqueta => $valor)
                    <div class="flex flex-col gap-1 py-3 sm:flex-row sm:justify-between">
                        <span class="text-sm text-gray-500">
                            {{ $etiqueta }}
                        </span>
                        <span class="break-words font-medium text-gray-800">
                            {{ $valor }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if (count($datosNuevos) > 0)
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-gray-800">
                Información registrada
            </h2>

            <div class="divide-y divide-gray-100">
                @foreach ($datosNuevos as $etiqueta => $valor)
                    <div class="flex flex-col gap-1 py-3 sm:flex-row sm:justify-between">
                        <span class="text-sm text-gray-500">
                            {{ $etiqueta }}
                        </span>
                        <span class="break-words font-medium text-gray-800">
                            {{ $valor }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Regresar --}}
    <div class="flex justify-end">
        <a href="{{ route('auditorias.index') }}"
           class="rounded-lg bg-blue-700 px-5 py-2 font-medium text-white transition hover:bg-blue-800">
            Volver al historial
        </a>
    </div>

</div>
@endsection
