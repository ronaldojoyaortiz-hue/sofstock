@extends('layouts.app')

@section('title', 'Proveedores')
@section('page-title', 'Proveedores')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">
                Proveedores
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Gestiona los proveedores de tu inventario.
            </p>
        </div>

        <a href="{{ route('proveedores.create') }}"
           class="inline-flex items-center rounded-lg bg-slate-900 px-5 py-3 text-sm font-medium text-white hover:bg-slate-700">
            Nuevo proveedor
        </a>
    </div>


    <x-card>

        @if ($proveedores->isEmpty())

            <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500">
                No hay proveedores registrados aún.
            </div>

        @else

            <div class="overflow-x-auto">

                <table class="w-full text-base text-left text-slate-600">

                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50">

                            <th class="px-5 py-4 font-semibold text-slate-800">
                                Razón social
                            </th>

                            <th class="px-5 py-4 font-semibold text-slate-800">
                                NIT
                            </th>

                            <th class="px-5 py-4 font-semibold text-slate-800">
                                Contacto
                            </th>

                            <th class="px-5 py-4 font-semibold text-slate-800">
                                Correo
                            </th>

                            <th class="px-5 py-4 font-semibold text-slate-800">
                                Teléfono
                            </th>

                            <th class="px-5 py-4 font-semibold text-slate-800">
                                Dirección
                            </th>

                            <th class="px-5 py-4 font-semibold text-slate-800">
                                Estado
                            </th>

                            <th class="px-5 py-4 font-semibold text-right text-slate-800">
                                Acciones
                            </th>

                        </tr>
                    </thead>


                    <tbody>

                        @foreach ($proveedores as $proveedor)

                            <tr class="border-b border-slate-200 last:border-0 hover:bg-slate-50">

                                <td class="px-5 py-4 font-medium text-slate-800">
                                    {{ $proveedor->razon_social }}
                                </td>


                                <td class="px-5 py-4">
                                    {{ $proveedor->nit ?? '-' }}
                                </td>


                                <td class="px-5 py-4">
                                    {{ $proveedor->contacto_nombre ?? '-' }}
                                </td>


                                <td class="px-5 py-4">
                                    {{ $proveedor->correo ?? '-' }}
                                </td>


                                <td class="px-5 py-4">
                                    {{ $proveedor->telefono ?? '-' }}
                                </td>


                                <td class="px-5 py-4">
                                    {{ $proveedor->direccion ?? '-' }}
                                </td>


                                <td class="px-5 py-4">

                                    <span class="inline-flex rounded-full px-3 py-1.5 text-sm font-medium
                                        {{ $proveedor->activo
                                            ? 'bg-emerald-100 text-emerald-700'
                                            : 'bg-slate-200 text-slate-600' }}">

                                        {{ $proveedor->activo ? 'Activo' : 'Inactivo' }}

                                    </span>

                                </td>


                                <td class="px-5 py-4 text-right">

                                    <div class="flex justify-end gap-3">

                                        {{-- Editar --}}

                                        <a href="{{ route('proveedores.edit', $proveedor->id_proveedor) }}"
                                           class="rounded-md p-2 text-blue-600 hover:bg-blue-50 hover:text-blue-900"
                                           title="Editar proveedor"
                                           aria-label="Editar proveedor">

                                            <svg class="h-6 w-6"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 viewBox="0 0 24 24"
                                                 aria-hidden="true">

                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      stroke-width="2"
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>

                                            </svg>

                                        </a>


                                        {{-- Eliminar --}}

                                        <form action="{{ route('proveedores.destroy', $proveedor->id_proveedor) }}"
                                              method="POST"
                                              onsubmit="return confirm('¿Deseas eliminar este proveedor?')">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="rounded-md p-2 text-red-600 hover:bg-red-50 hover:text-red-900"
                                                    title="Eliminar proveedor"
                                                    aria-label="Eliminar proveedor">

                                                <svg class="h-6 w-6"
                                                     fill="none"
                                                     stroke="currentColor"
                                                     viewBox="0 0 24 24"
                                                     aria-hidden="true">

                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 01-1 1v3M4 7h16">
                                                    </path>

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


