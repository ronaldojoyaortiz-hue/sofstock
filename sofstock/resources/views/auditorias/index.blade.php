@extends('layouts.app')

@section('title', 'Historial de auditoría')

@section('content')
<div class="space-y-6">

    {{-- Tabla de auditoría --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 px-5 py-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-lg font-semibold text-gray-800">
                    Historial de actividades
                </h2>
                @if (auth()->user()?->esAdministrador() || auth()->user()?->can('eliminar auditorias'))
                    <form id="eliminar-auditorias-seleccionadas" method="POST" action="{{ route('auditorias.destroy-selected') }}"
                        onsubmit="return confirm('¿Deseas eliminar las auditorías seleccionadas de tu historial?')">
                        @csrf
                        @method('DELETE')
                        <input id="seleccionar-todas-auditorias-valor" type="hidden" name="seleccionar_todas" value="0">
                        <div id="acciones-auditorias-seleccionadas" class="hidden items-center gap-3">
                            <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                                <input id="seleccionar-todas-auditorias" type="checkbox"
                                    class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                Seleccionar todas las auditorías (todas las páginas)
                            </label>
                            <span id="seleccionar-todas-auditorias-ayuda" class="hidden text-sm text-slate-600">
                                Se incluirán también las auditorías de las demás páginas.
                            </span>
                            <button id="eliminar-auditorias-seleccionadas-boton" type="submit" disabled
                                class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50">
                                Eliminar
                            </button>
                        </div>
                    </form>
                @endif
            </div>
            @if (session('success'))
                <p class="mt-2 text-sm text-emerald-700" role="status">{{ session('success') }}</p>
            @endif
        </div>

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-50">
                    <tr>
                        @if (auth()->user()?->esAdministrador() || auth()->user()?->can('eliminar auditorias'))
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-600">
                                Seleccionar
                            </th>
                        @endif
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                            Fecha
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                            Usuario
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                            Acción
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                            Módulo
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                            Descripción
                        </th>

                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-600">
                            Acciones
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 bg-white">

                    @forelse ($auditorias as $auditoria)

                        <tr class="transition hover:bg-gray-50">
                            @if (auth()->user()?->esAdministrador() || auth()->user()?->can('eliminar auditorias'))
                                <td class="px-4 py-3 text-center">
                                    <input type="checkbox" name="auditorias[]" value="{{ $auditoria->id_auditoria }}"
                                        form="eliminar-auditorias-seleccionadas"
                                        class="auditoria-seleccionada rounded border-gray-300 text-red-600 focus:ring-red-500"
                                        aria-label="Seleccionar auditoría {{ $auditoria->id_auditoria }}">
                                </td>
                            @endif

                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">
                                {{ $auditoria->created_at?->format('d/m/Y H:i:s') ?? 'Sin fecha' }}
                            </td>

                            <td class="px-4 py-3 text-sm text-gray-700">
                                @if ($auditoria->usuario)
                                    {{ $auditoria->usuario->nombre }}
                                    {{ $auditoria->usuario->apellido ?? '' }}
                                @else
                                    <span class="text-gray-500">
                                        Usuario eliminado / Sistema
                                    </span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-sm">
                                @php
                                    $colorAccion = match ($auditoria->accion) {
                                        'CREAR' => 'bg-green-100 text-green-800',
                                        'ACTUALIZAR' => 'bg-blue-100 text-blue-800',
                                        'ELIMINAR', 'ELIMINAR_TODO' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                @endphp

                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $colorAccion }}">
                                    {{ str_replace('_', ' ', $auditoria->accion) }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">
                                {{ $auditoria->modulo }}
                            </td>

                            <td class="max-w-xs px-4 py-3 text-sm text-gray-700">
                                {{ $auditoria->descripcion }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-center">
                                <div class="inline-flex items-center gap-2">
                                <a
                                    href="{{ route('auditorias.show', $auditoria->id_auditoria) }}"
                                    class="inline-flex rounded-md bg-slate-100 p-2 text-slate-700 transition hover:bg-slate-200 hover:text-slate-900"
                                    title="Ver detalle"
                                    aria-label="Ver detalle de auditoría">
                                    <span class="block h-5 w-5" aria-hidden="true">@include('layouts.partials.icons', ['icon' => 'eye'])</span>
                                </a>
                                @can('eliminar auditorias')
                                    <form method="POST" action="{{ route('auditorias.destroy', $auditoria->id_auditoria) }}"
                                        onsubmit="return confirm('La auditoría solo se ocultará de tu historial. Los demás usuarios seguirán viéndola. ¿Continuar?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex rounded-md bg-red-50 p-2 text-red-700 transition hover:bg-red-100 hover:text-red-900"
                                            title="Ocultar de mi historial"
                                            aria-label="Ocultar auditoría de mi historial">
                                            <span class="block h-5 w-5" aria-hidden="true">@include('layouts.partials.icons', ['icon' => 'trash'])</span>
                                        </button>
                                    </form>
                                @endcan
                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="{{ auth()->user()?->esAdministrador() || auth()->user()?->can('eliminar auditorias') ? 7 : 6 }}"
                                class="px-4 py-10 text-center text-sm text-gray-500">
                                No hay registros de auditoría para mostrar.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

        {{-- Paginación --}}
        <div class="border-t border-gray-200 px-5 py-4">
            {{ $auditorias->links() }}
        </div>

    </div>

    @if (auth()->user()?->esAdministrador() || auth()->user()?->can('eliminar auditorias'))
        <script>
            const seleccionarTodas = document.getElementById('seleccionar-todas-auditorias');
            const auditoriasSeleccionadas = document.querySelectorAll('.auditoria-seleccionada');
            const botonEliminarSeleccionadas = document.getElementById('eliminar-auditorias-seleccionadas-boton');
            const accionesSeleccionadas = document.getElementById('acciones-auditorias-seleccionadas');
            const seleccionarTodasValor = document.getElementById('seleccionar-todas-auditorias-valor');
            const seleccionarTodasAyuda = document.getElementById('seleccionar-todas-auditorias-ayuda');
            const actualizarSeleccion = () => {
                const seleccionadas = Array.from(auditoriasSeleccionadas).filter((checkbox) => checkbox.checked).length;
                const todasSeleccionadas = seleccionarTodasValor.value === '1';
                const haySeleccion = seleccionadas > 0 || todasSeleccionadas;
                accionesSeleccionadas.classList.toggle('hidden', !haySeleccion);
                accionesSeleccionadas.classList.toggle('flex', haySeleccion);
                botonEliminarSeleccionadas.disabled = !haySeleccion;

                if (!haySeleccion) {
                    seleccionarTodas.checked = false;
                }
            };

            seleccionarTodas?.addEventListener('change', function () {
                seleccionarTodasValor.value = this.checked ? '1' : '0';
                seleccionarTodasAyuda.classList.toggle('hidden', !this.checked);
                actualizarSeleccion();
            });
            auditoriasSeleccionadas.forEach((checkbox) => {
                checkbox.addEventListener('change', actualizarSeleccion);
            });
        </script>
    @endif

</div>
@endsection
