@extends('layouts.app')

@section('title', 'Categorías')
@section('page-title', 'Categorías')

@section('content')


<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">

    <div>
        <h1 class="text-2xl font-bold text-slate-800">
            Categorías
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Administra las categorías de tus productos.
        </p>
    </div>

    <x-button href="{{ route('categorias.create') }}">
        Nueva categoría
    </x-button>

</div>


<x-card :padding="false">

    <div class="overflow-x-auto">

        <table class="min-w-full divide-y divide-slate-100 text-sm">

            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">

                <tr>

                    <th class="px-5 py-3.5 font-semibold">
                        ID
                    </th>

                    <th class="px-5 py-3.5 font-semibold">
                        Nombre
                    </th>

                    <th class="px-5 py-3.5 font-semibold">
                        Descripción
                    </th>

                    <th class="px-5 py-3.5 font-semibold">
                        Estado
                    </th>

                    <th class="px-5 py-3.5 text-right font-semibold">
                        Acciones
                    </th>

                </tr>

            </thead>


            <tbody class="divide-y divide-slate-100 bg-white text-slate-600">

                @forelse ($categorias as $categoria)

                    <tr class="hover:bg-slate-50/70">

                        <!-- ID -->
                        <td class="px-5 py-4 font-medium text-slate-800">
                            {{ $categoria->id_categoria }}
                        </td>


                        <!-- Nombre -->
                        <td class="px-5 py-4 font-medium text-slate-700">
                            {{ $categoria->nombre }}
                        </td>


                        <!-- Descripción -->
                        <td class="max-w-md px-5 py-4 text-slate-500">
                            {{ $categoria->descripcion ?: 'Sin descripción' }}
                        </td>


                        <!-- Estado -->
                        <td class="px-5 py-4">

                            <x-badge
                                :status="$categoria->activa ? 'Activo' : 'Inactivo'"
                            />

                        </td>


                        <!-- Acciones -->
                        <td class="px-5 py-4">

                            <div class="flex justify-end items-center gap-2">

                                <!-- Editar -->
                                <a href="{{ route('categorias.edit', $categoria->id_categoria) }}"
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
                                <form action="{{ route('categorias.destroy', $categoria->id_categoria) }}"
                                    method="POST"
                                    onsubmit="return confirm('¿Estás seguro de eliminar esta categoría?');">

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

                        <td colspan="5"
                            class="px-5 py-12 text-center text-slate-500">

                            No hay categorías registradas.

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</x-card>


@endsection

