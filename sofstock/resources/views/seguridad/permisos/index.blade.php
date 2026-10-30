@extends('layouts.app')

@section('title', 'Permisos asignados')
@section('page-title', 'Permisos asignados')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Permisos asignados</h1>
            <p class="mt-1 text-sm text-slate-500">Consulta y quita permisos individuales de los roles.</p>
        </div>
        <x-button href="{{ route('seguridad.permisos.index') }}">Volver a roles</x-button>
    </div>

    @if (session('success'))
        <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <x-card>
        @if ($assignments->isEmpty())
            <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">
                Todavía no hay permisos asignados a roles.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-100">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">ID del rol</th>
                            <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Rol</th>
                            <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Permiso asignado</th>
                            <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($assignments as $assignment)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-4 font-mono text-sm text-slate-600">{{ $assignment->role->id }}</td>
                                <td class="whitespace-nowrap px-5 py-4 font-medium capitalize text-slate-800">{{ $assignment->role->name }}</td>
                                <td class="px-5 py-4 text-sm text-slate-700">{{ $assignment->permission->name }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('seguridad.permisos.assignment.show', [$assignment->role->id, $assignment->permission->id]) }}"
                                           class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-900"
                                           title="Ver asignación"
                                           aria-label="Ver permiso {{ $assignment->permission->name }} del rol {{ $assignment->role->name }}">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                        <a href="{{ route('seguridad.permisos.roles', ['role_id' => $assignment->role->id, 'gestionar' => 1]) }}"
                                           class="rounded-md p-2 text-blue-600 hover:bg-blue-50 hover:text-blue-900"
                                           title="Editar permisos del rol"
                                           aria-label="Editar permisos del rol {{ $assignment->role->name }}">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                        <form action="{{ route('seguridad.permisos.assignment.destroy', [$assignment->role->id, $assignment->permission->id]) }}"
                                              method="POST"
                                              onsubmit="return confirm('¿Deseas quitar este permiso solamente de este rol?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="rounded-md p-2 text-red-600 hover:bg-red-50 hover:text-red-900"
                                                    title="Quitar permiso del rol"
                                                    aria-label="Quitar permiso {{ $assignment->permission->name }} del rol {{ $assignment->role->name }}">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>
@endsection
