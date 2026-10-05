@extends('layouts.app')

@section('title', 'Proveedores')
@section('page-title', 'Proveedores')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Proveedores</h1>
            <p class="mt-1 text-sm text-slate-500">Gestiona los proveedores de tu inventario.</p>
        </div>
        <a href="{{ route('proveedores.create') }}" class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
            Nuevo proveedor
        </a>
    </div>

    <x-card>
        @if ($proveedores->isEmpty())
            <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">
                No hay proveedores registrados aún.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm text-slate-700">
                    <thead>
                        <tr class="border-b border-slate-200">
                            <th class="px-4 py-3 font-semibold">Razón social</th>
                            <th class="px-4 py-3 font-semibold">NIT</th>
                            <th class="px-4 py-3 font-semibold">Contacto</th>
                            <th class="px-4 py-3 font-semibold">Correo</th>
                            <th class="px-4 py-3 font-semibold">Teléfono</th>
                            <th class="px-4 py-3 font-semibold">Dirección</th>
                            <th class="px-4 py-3 font-semibold">Estado</th>
                            <th class="px-4 py-3 font-semibold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($proveedores as $proveedor)
                            <tr class="border-b border-slate-200 last:border-0">
                                <td class="px-4 py-3">{{ $proveedor->razon_social }}</td>
                                <td class="px-4 py-3">{{ $proveedor->nit ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $proveedor->contacto_nombre ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $proveedor->correo ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $proveedor->telefono ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $proveedor->direccion ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-1 text-xs font-medium {{ $proveedor->activo ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                                        {{ $proveedor->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('proveedores.edit', $proveedor->id_proveedor) }}" class="p-1 text-blue-600 hover:text-blue-900" title="Editar proveedor" aria-label="Editar proveedor">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                        <form action="{{ route('proveedores.destroy', $proveedor->id_proveedor) }}" method="POST" onsubmit="return confirm('¿Deseas eliminar este proveedor?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 text-red-600 hover:text-red-900" title="Eliminar proveedor" aria-label="Eliminar proveedor">
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