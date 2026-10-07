@extends('layouts.app')

@section('title', 'Productos')

@section('page-title', 'Productos')

@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-800">
        Productos
    </h1>

    <p class="mt-1 text-sm text-slate-500">
        Lista de productos en tu inventario.
    </p>
</div>


<x-card class="max-w-6xl">

    <div class="flex justify-between items-center mb-4">

        <h2 class="text-lg font-semibold text-slate-800">
            Lista de productos
        </h2>

        <x-button href="{{ route('productos.create') }}">
            Agregar producto
        </x-button>

    </div>


    <div class="overflow-x-auto">

        <table class="min-w-full divide-y divide-slate-200">

            <thead class="bg-slate-100">

                <tr>

                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                        Nombre
                    </th>

                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                        Precio
                    </th>

                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                        Stock
                    </th>

                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                        Categoría
                    </th>

                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                        Proveedor
                    </th>

                    <th scope="col"
                        class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">
                        Acciones
                    </th>

                </tr>

            </thead>


            <tbody class="bg-white divide-y divide-slate-200">

                @forelse ($productos as $producto)

                    <tr class="hover:bg-slate-50">

                        <!-- Nombre -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $producto->nombre }}
                        </td>


                        <!-- Precio -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            ${{ number_format($producto->precio_base, 2) }}
                        </td>


                        <!-- Stock -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ number_format((float) $producto->stock_actual, 0, ',', '.') }}
                        </td>


                        <!-- Categoría -->
                        <td class="px-6 py-4 whitespace-nowrap">

                            {{ $producto->categoria->nombre ?? 'Sin categoría' }}

                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">

                            {{ $producto->proveedor->razon_social ?? 'Sin proveedor' }}

                        </td>


                        <!-- Acciones -->
                        <td class="px-6 py-4 whitespace-nowrap">

                            <div class="flex items-center justify-center gap-2">

                                <!-- Editar -->
                                <a href="{{ route('productos.edit', $producto->id) }}"
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
                                <form action="{{ route('productos.destroy', $producto->id) }}"
                                    method="POST"
                                    onsubmit="return confirm('¿Estás seguro de que deseas eliminar este producto?');">

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

                        <td colspan="6"
                            class="px-6 py-8 text-center text-slate-500">

                            No hay productos registrados.

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</x-card>


@endsection
