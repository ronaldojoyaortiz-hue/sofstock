@extends('layouts.app')

@section('title', $modulo['titulo'])
@section('page-title', $modulo['titulo'])

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('consultas.index') }}" class="text-sm font-medium text-primary-700 hover:text-primary-900">← Todas las consultas</a>
            <h1 class="mt-2 text-2xl font-bold text-slate-800">{{ $modulo['titulo'] }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $modulo['descripcion'] }} Los registros aparecen al abrir esta página; puedes filtrar por un criterio.</p>
        </div>
    </div>

    @if ($tipoConsulta === 'reportes' && !$reportesDisponibles)
        <div class="mb-6 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Los datos financieros aún no están disponibles en la base de datos. Consulta con el administrador antes de aplicar las migraciones pendientes.
        </div>
    @endif
    @if ($tipoConsulta === 'inmuebles')
        <p class="mb-4 text-xs text-slate-500">La disponibilidad se determina por el estado registrado: solo los inmuebles con estado “Disponible” se muestran como disponibles.</p>
    @endif

    <form method="GET" action="{{ route("consultas.{$tipoConsulta}.index") }}" class="mb-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-4">
            <label for="consulta-criterio" class="mb-1 block text-sm font-medium text-slate-700">Buscar por</label>
            <select id="consulta-criterio" name="criterio" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200 sm:max-w-md">
                @foreach ($definicionFiltros as $campo)
                    <option value="{{ $campo['nombre'] }}" @selected($criterio === $campo['nombre'])>{{ $campo['etiqueta'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($definicionFiltros as $campo)
                <div data-consulta-campo="{{ $campo['nombre'] }}" @if ($criterio !== $campo['nombre']) hidden @endif>
                    <label for="filtro-{{ $campo['nombre'] }}" class="mb-1 block text-sm font-medium text-slate-700">{{ $campo['etiqueta'] }}</label>
                    @if ($campo['tipo'] === 'select')
                        <select id="filtro-{{ $campo['nombre'] }}" name="{{ $campo['nombre'] }}" @disabled($criterio !== $campo['nombre']) class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
                            <option value="">Todos</option>
                            @if (isset($campo['valores']))
                                @foreach ($campo['valores'] as $valor => $etiqueta)
                                    <option value="{{ $valor }}" @selected(($filtros[$campo['nombre']] ?? '') == $valor)>{{ $etiqueta }}</option>
                                @endforeach
                            @else
                                @foreach (($opciones[$campo['fuente']] ?? []) as $opcion)
                                    @php
                                        $esTextoSimple = is_string($opcion);
                                        $valor = $esTextoSimple ? $opcion : data_get($opcion, $campo['valor'] ?? 'valor');
                                        $etiqueta = $esTextoSimple
                                            ? $opcion
                                            : data_get($opcion, $campo['texto'] ?? 'etiqueta', $valor);
                                    @endphp
                                    <option value="{{ $valor }}" @selected(($filtros[$campo['nombre']] ?? '') == $valor)>{{ $etiqueta }}</option>
                                @endforeach
                            @endif
                        </select>
                    @else
                        <div class="flex gap-2">
                            <input
                                id="filtro-{{ $campo['nombre'] }}"
                                name="{{ $campo['nombre'] }}"
                                type="{{ $campo['tipo'] }}"
                                value="{{ $filtros[$campo['nombre']] ?? '' }}"
                                @disabled($criterio !== $campo['nombre'])
                                @if ($campo['tipo'] === 'number') step="any" min="0" @endif
                                class="min-w-0 flex-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200"
                            >
                            @if ($campo['tipo'] === 'date')
                                <button
                                    type="button"
                                    data-date-picker="{{ $campo['nombre'] }}"
                                    @disabled($criterio !== $campo['nombre'])
                                    class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 3v2m8-2v2M4 9h16M5 5h14a1 1 0 0 1 1 1v13H4V6a1 1 0 0 1 1-1z" />
                                    </svg>
                                    Elegir fecha
                                </button>
                            @endif
                        </div>
                    @endif
                    @error($campo['nombre']) <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            @endforeach
        </div>
        <div class="mt-4 flex flex-wrap justify-end gap-2">
            <a href="{{ route("consultas.{$tipoConsulta}.index") }}" class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Limpiar</a>
            <button type="submit" class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">Buscar</button>
        </div>
    </form>

    @if ($tipoConsulta === 'reportes' && $reportesDisponibles)
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                'Ingresos por ventas pagadas' => $resumen['ingresos'] ?? 0,
                'Ganancia neta estimada' => $resumen['ganancia_neta'] ?? 0,
                'Pérdidas totales' => $resumen['perdidas_totales'] ?? 0,
                'Valor de productos dañados' => $resumen['perdidas_mermas'] ?? 0,
            ] as $etiqueta => $valor)
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">{{ $etiqueta }}</p>
                    <p class="mt-2 text-xl font-bold text-slate-800">${{ number_format((float) $valor, 2, ',', '.') }}</p>
                </div>
            @endforeach
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Registros de productos dañados</p>
                <p class="mt-2 text-xl font-bold text-slate-800">{{ number_format($resumen['registros_danados'] ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Productos dañados distintos</p>
                <p class="mt-2 text-xl font-bold text-slate-800">{{ number_format($resumen['productos_danados'] ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Inversiones en productos</p>
                <p class="mt-2 text-xl font-bold text-slate-800">${{ number_format((float) ($resumen['inversiones'] ?? 0), 2, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Otros egresos</p>
                <p class="mt-2 text-xl font-bold text-slate-800">${{ number_format((float) ($resumen['gastos'] ?? 0), 2, ',', '.') }}</p>
            </div>
        </div>
        <p class="mb-4 text-xs text-slate-500">La ganancia neta estimada se calcula con las ventas pagadas menos inversiones, gastos y pérdidas por productos dañados.</p>
    @endif

    @if ($filas)
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            @foreach ($columnas as $columna)
                                <th scope="col" class="whitespace-nowrap px-5 py-3">{{ $columna }}</th>
                            @endforeach
                            <th scope="col" class="px-5 py-3 text-right">Detalle</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-600">
                        @forelse ($filas as $fila)
                            <tr class="hover:bg-slate-50">
                                @foreach ($columnas as $columna)
                                    <td class="whitespace-nowrap px-5 py-4">{{ data_get($fila['campos'], $columna, '—') ?: '—' }}</td>
                                @endforeach
                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    <a href="{{ $fila['url'] }}" class="font-medium text-primary-700 hover:text-primary-900">Ver información</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($columnas) + 1 }}" class="px-5 py-12 text-center text-slate-500">No se encontraron registros con esos criterios.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-4">{{ $filas->links() }}</div>
        </div>
    @else
        <div class="rounded-xl border border-dashed border-slate-300 bg-white px-5 py-10 text-center text-sm text-slate-500">
            No hay registros disponibles para mostrar.
        </div>
    @endif

    <script>
        const criterioConsulta = document.getElementById('consulta-criterio');
        const camposConsulta = document.querySelectorAll('[data-consulta-campo]');

        criterioConsulta.addEventListener('change', () => {
            camposConsulta.forEach((contenedor) => {
                const activo = contenedor.dataset.consultaCampo === criterioConsulta.value;
                contenedor.hidden = !activo;
                contenedor.querySelectorAll('input, select, button').forEach((control) => {
                    control.disabled = !activo;
                });
            });
        });

        document.querySelectorAll('[data-date-picker]').forEach((boton) => {
            boton.addEventListener('click', () => {
                const campoFecha = document.getElementById(`filtro-${boton.dataset.datePicker}`);
                if (typeof campoFecha.showPicker === 'function') {
                    campoFecha.showPicker();
                } else {
                    campoFecha.focus();
                }
            });
        });
    </script>
@endsection
