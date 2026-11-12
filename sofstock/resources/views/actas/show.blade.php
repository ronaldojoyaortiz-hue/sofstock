@extends('layouts.app')

@section('title', 'Detalle del acta')
@section('page-title', 'Detalle del acta')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">

@if(session('success'))
    <div class="rounded-lg bg-green-100 p-3 text-green-800">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="rounded-lg bg-red-100 p-3 text-red-700">
        {{ $errors->first() }}
    </div>
@endif

<div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">{{ $acta->titulo }}</h2>
            <p class="text-sm text-gray-500">Código: {{ $acta->codigo }}</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('actas.pdf', $acta) }}"
               class="rounded-lg bg-red-700 px-4 py-2 text-white hover:bg-red-800">
                Descargar acta en PDF
            </a>
            <a href="{{ route('actas.index') }}"
               class="rounded-lg border border-gray-300 px-4 py-2 text-gray-700">
                Volver
            </a>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6 space-y-5">

        <div class="border-b border-gray-300 pb-4 text-center">
            <h1 class="text-2xl font-bold text-gray-900">{{ $acta->titulo }}</h1>
            <p class="mt-1 text-sm text-gray-600">Acta {{ $acta->codigo }}</p>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <p class="text-sm text-gray-500">Tipo de acta</p>
                <p class="font-medium">
                    {{ [
                        'entrega' => 'Acta de entrega',
                        'recibo' => 'Acta de recibo',
                        'junta_directiva' => 'Junta directiva',
                        'asamblea' => 'Asamblea',
                        'inventario' => 'Acta de inventario',
                        'devolucion' => 'Acta de devolución',
                    ][$acta->tipo_acta] ?? $acta->tipo_acta }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Fecha</p>
                <p class="font-medium">{{ $acta->fecha?->format('d/m/Y') }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Hora</p>
                <p class="font-medium">{{ $acta->hora ?: 'No registrada' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Lugar</p>
                <p class="font-medium">{{ $acta->lugar ?: 'No registrado' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Responsable del registro</p>
                <p class="font-medium">{{ $acta->usuario_nombre }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Rol al registrar</p>
                <p class="font-medium">{{ $acta->rol_nombre ?: 'No registrado' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Estado</p>
                <p class="font-medium">{{ ucfirst(str_replace('_', ' ', $acta->estado)) }}</p>
            </div>

            @if(in_array($acta->estado, ['borrador', 'pendiente_aprobacion'], true) && auth()->user()->can('editar actas'))
                <div class="sm:col-span-2 rounded-lg border border-blue-200 bg-blue-50 p-4">
                    <form action="{{ route('actas.estado', $acta) }}" method="POST" class="flex flex-wrap items-end gap-3">
                        @csrf
                        @method('PATCH')
                        <div class="min-w-52 flex-1">
                            <label for="estado" class="mb-1 block text-sm font-medium text-gray-700">Cambiar estado</label>
                            <select id="estado" name="estado" required class="w-full rounded-lg border-gray-300">
                                @foreach([
                                    'borrador' => 'Borrador',
                                    'pendiente_aprobacion' => 'Emitida / pendiente de aprobación',
                                    'aprobada' => 'Aprobada',
                                    'firmada' => 'Firmada',
                                    'anulada' => 'Anulada',
                                ] as $valor => $etiqueta)
                                    <option value="{{ $valor }}" @selected($acta->estado === $valor)>{{ $etiqueta }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="rounded-lg bg-blue-700 px-4 py-2 text-white hover:bg-blue-800">
                            Guardar estado
                        </button>
                    </form>
                </div>
            @endif

            @if($acta->inmueble)
                <div class="sm:col-span-2">
                    <p class="text-sm text-gray-500">Inmueble relacionado</p>
                    <p class="font-medium">
                        {{ $acta->inmueble->codigo }} · {{ $acta->inmueble->tipo_inmueble }} ·
                        {{ $acta->inmueble->direccion }}, {{ $acta->inmueble->ciudad }}
                    </p>
                </div>
            @endif
        </div>

        <hr>

        @if(count($acta->productos))
            <div>
                <h3 class="mb-2 font-semibold text-gray-800">Productos relacionados</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left">Producto</th>
                                <th class="px-3 py-2 text-left">Cantidad</th>
                                <th class="px-3 py-2 text-left">Observaciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($acta->productos as $producto)
                                <tr>
                                    <td class="px-3 py-2">{{ $producto['nombre'] }}</td>
                                    <td class="px-3 py-2">{{ $producto['cantidad'] }}</td>
                                    <td class="px-3 py-2">{{ $producto['observaciones'] ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div>
            <h3 class="mb-2 font-semibold text-gray-800">Objetivo</h3>
            <p class="whitespace-pre-line text-gray-700">{{ $acta->objetivo ?: 'Sin información.' }}</p>
        </div>

        <div>
            <h3 class="mb-2 font-semibold text-gray-800">Desarrollo</h3>
            <p class="whitespace-pre-line text-gray-700">{{ $acta->desarrollo ?: 'Sin información.' }}</p>
        </div>

        <div>
            <h3 class="mb-2 font-semibold text-gray-800">Conclusiones</h3>
            <p class="whitespace-pre-line text-gray-700">{{ $acta->conclusiones ?: 'Sin información.' }}</p>
        </div>

        <hr>

        <div>
            <h3 class="mb-6 font-semibold text-gray-800">Firmas</h3>
            <div class="grid grid-cols-1 gap-10 sm:grid-cols-2">
                @foreach($acta->firmantesParaMostrar() as $firmante)
                    <div class="pt-10 text-center">
                        <div class="border-t border-gray-500 pt-2">
                            <p class="font-medium">{{ $firmante['nombre'] ?? '' }}</p>
                            <p class="text-sm text-gray-500">{{ ucfirst($firmante['cargo'] ?? $firmante['rol'] ?? '') }}</p>
                            <p class="mt-1 text-xs text-gray-500">Firma</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</div>
@endsection
