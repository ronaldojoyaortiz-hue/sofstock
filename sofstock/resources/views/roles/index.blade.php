@extends('layouts.app')

@section('title', 'Roles')
@section('page-title', 'Roles')

@section('content')

<div class="mb-6">

    <h1 class="text-2xl font-bold text-slate-800">
        Roles
    </h1>

    <p class="mt-1 text-sm text-slate-500">
        Gestiona los roles de usuario y sus permisos.
    </p>

</div>


<x-card>

    <!-- Botón Nuevo rol -->
    <div class="flex justify-end mb-4">

        <x-button href="{{ route('roles.create') }}">
            Nuevo rol
        </x-button>

    </div>


    <!-- Tabla -->
    <div class="overflow-x-auto">

        <table class="w-full border-collapse border border-slate-200">

            <thead>

                <tr class="bg-slate-100">

                    <th class="border border-slate-200 px-4 py-2 text-left">
                        Nombre
                    </th>

                    <th class="border border-slate-200 px-4 py-2 text-center">
                        Acciones
                    </th>

                </tr>

            </thead>


            <tbody>

                @forelse ($roles as $rol)

                    <tr class="hover:bg-slate-50">

                        <!-- Nombre -->
                        <td class="border border-slate-200 px-4 py-2">
                            {{ $rol->nombre_rol }}
                        </td>


                        <!-- Acciones -->
                        <td class="border border-slate-200 px-4 py-2">

                            <div class="flex items-center justify-center gap-2">

                                <!-- Editar -->
                                <a href="{{ route('roles.edit', $rol->id_rol) }}"
                                    class="text-blue-600 hover:text-blue-900 p-1"
                                    title="Editar">

                                    <svg class="w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24">

                                        <path stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                        </path>

                                    </svg>

                                </a>


                                <!-- Eliminar -->
                                <form action="{{ route('roles.destroy', $rol->id_rol) }}"
                                    method="POST"
                                    onsubmit="return confirm('¿Estás seguro de que deseas eliminar este rol?');">

                                    @csrf

                                    @method('DELETE')

                                    <button type="submit"
                                        class="text-red-600 hover:text-red-900 p-1"
                                        title="Eliminar">

                                        <svg class="w-5 h-5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24">

                                            <path stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>

                                        </svg>

                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="2"
                            class="border border-slate-200 px-4 py-8 text-center text-slate-500">

                            No hay roles registrados.

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</x-card>


@endsection
