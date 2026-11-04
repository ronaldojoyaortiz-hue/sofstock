 @extends('layouts.app')

@section('title', 'Actas')
@section('page-title', 'Gestión de actas')

@section('content')
@php
    $usuario = auth()->user();
    $esAdministrador = $usuario->hasRole('administrador')
        || mb_strtolower(trim($usuario->rol?->nombre_rol ?? '')) === 'administrador';
@endphp

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Actas registradas</h2>
            <p class="text-sm text-gray-500">Consulta y administra las actas de la cooperativa.</p>
        </div>

        @if ($esAdministrador || $usuario->can('crear actas'))
            <a href="{{ route('actas.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-blue-700 px-4 py-2 text-white hover:bg-blue-800">
                Crear acta
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="rounded-lg bg-green-100 p-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-semibold">Código</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold">Título</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold">Tipo</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold">Fecha</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold">Responsable</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold">Relacionado</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold">Estado</th>
                    <th class="px-4 py-3 text-center text-sm font-semibold">Acciones</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @forelse($actas as $acta)
                    <tr>
                        <td class="px-4 py-3 text-sm">{{ $acta->codigo }}</td>
                        <td class="px-4 py-3 text-sm">{{ $acta->titulo }}</td>
                        <td class="px-4 py-3 text-sm">
                            {{ [
                                'entrega_bienes' => 'Entrega de bienes',
                                'junta_directiva' => 'Junta directiva',
                                'asamblea' => 'Reunión o asamblea',
                                'entrega_inmueble' => 'Entrega de inmueble'
                            ][$acta->tipo] ?? $acta->tipo }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            {{ $acta->fecha?->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $acta->usuario_nombre }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if($acta->inmueble)
                                {{ $acta->inmueble->codigo }}
                            @elseif($acta->productos->isNotEmpty())
                                {{ $acta->productos->pluck('nombre')->join(', ') }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                            {{ ucfirst(str_replace('_', ' ', $acta->estado)) }}
                        </td>
                        <td class="px-4 py-3 text-center text-sm">
                            <div class="flex items-center justify-center gap-3">
                                <a href="{{ route('actas.show', $acta->id) }}" class="text-sky-700 transition hover:text-sky-900" title="Ver acta" aria-label="Ver acta">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z" /><circle cx="12" cy="12" r="3" /></svg>
                                </a>

                                @can('editar actas')
                                    <a href="{{ route('actas.edit', $acta->id) }}" class="text-blue-600 transition hover:text-blue-800" title="Editar acta" aria-label="Editar acta">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" aria-hidden="true"><path d="M12 20h9" /><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z" /><path d="M15 5l4 4" /></svg>
                                    </a>
                                @endcan

                                @can('eliminar actas')
                                    <form action="{{ route('actas.destroy', $acta->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('¿Deseas eliminar esta acta?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 transition hover:text-red-800" title="Eliminar acta" aria-label="Eliminar acta">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" aria-hidden="true"><path d="M3 6h18" /><path d="M8 6V4h8v2" /><path d="m19 6-1 14H6L5 6" /><path d="M10 11v5M14 11v5" /></svg>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                            No hay actas registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $actas->links() }}
    </div>
</div>
@endsection
