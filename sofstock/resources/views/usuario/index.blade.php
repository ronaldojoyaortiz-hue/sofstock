@extends('layouts.app')
 
@section('title', 'Usuarios')
@section('page-title', 'Usuarios')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Usuarios</h1>
            <p class="mt-1 text-sm text-slate-500">Gestiona los usuarios de tu sistema.</p>
        </div>
        <a href="{{ route('usuarios.create') }}" class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
            Nuevo usuario
        </a>
    </div>

    <x-card>
        @if ($usuarios->isEmpty())
            <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">
                No hay usuarios registrados aún.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-100">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nombre</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Correo</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Rol</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Estado</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @foreach ($usuarios as $usuario)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $usuario->nombre }} {{ $usuario->apellido }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $usuario->correo }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $usuario->rol->nombre_rol }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="rounded-full px-2 py-1 text-xs font-medium {{ $usuario->activo ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                                        {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('usuarios.edit', $usuario->id) }}"
                                           class="rounded-md p-2 text-blue-600 hover:bg-blue-50 hover:text-blue-900"
                                           title="Editar usuario"
                                           aria-label="Editar usuario">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                        <form action="{{ route('usuarios.destroy', $usuario->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('¿Deseas eliminar este usuario?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="rounded-md p-2 text-red-600 hover:bg-red-50 hover:text-red-900"
                                                    title="Eliminar usuario"
                                                    aria-label="Eliminar usuario">
                                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 01-1 1v3M4 7h16"></path>
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

            <div class="mt-4">
                {{ $usuarios->links() }}

            </div>
        @endif
    </x-card>
@endsection