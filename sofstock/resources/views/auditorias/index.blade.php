@extends('layouts.app')

@section('title', 'Historial de auditoría')

@section('content')
<div class="space-y-6">

    {{-- Tabla de auditoría --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-800">
                Historial de actividades
            </h2>
        </div>

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-50">
                    <tr>
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
                            Detalle
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 bg-white">

                    @forelse ($auditorias as $auditoria)

                        <tr class="transition hover:bg-gray-50">

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
                                        'ELIMINAR' => 'bg-red-100 text-red-800',
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
                                <a
                                    href="{{ route('auditoria.show', $auditoria->id_auditoria) }}"
                                    class="inline-flex rounded-lg bg-blue-700 px-3 py-2 text-sm font-medium text-white transition hover:bg-blue-800">
                                    Ver detalle
                                </a>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6"
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

</div>
@endsection
