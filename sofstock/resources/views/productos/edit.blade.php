@extends('layouts.app')

@section('title', 'Editar producto')
@section('page-title', 'Editar producto')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Editar producto</h1>
        <p class="mt-1 text-sm text-slate-500">Actualiza la información del producto.</p>
    </div>

    <style>
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type="number"] {
            -moz-appearance: textfield;
            appearance: textfield;
        }
    </style>

    <x-card class="max-w-2xl">
        <form action="{{ route('productos.update', $producto->id) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <x-input name="nombre" label="Nombre" :value="old('nombre', $producto->nombre)" required />
            <x-input name="precio_base" label="Precio" type="number" step="0.01" min="0" :value="old('precio_base', $producto->precio_base)" required />
            <x-input name="stock_actual" label="Stock" type="number" step="1" min="0" :value="old('stock_actual', $producto->stock_actual)" required />
            <div><label for="descripcion" class="mb-1.5 block text-sm font-medium text-slate-700">Descripción</label><textarea id="descripcion" name="descripcion" rows="3" class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm">{{ old('descripcion', $producto->descripcion) }}</textarea></div>

            <div>
                <label for="categoria_id" class="mb-1.5 block text-sm font-medium text-slate-700">Categoría</label>
                <select id="id_categoria" name="id_categoria" class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-primary-500">
                    @foreach ($categorias as $categoria)
                        <option value="{{ $categoria->id_categoria }}" @selected(old('id_categoria', $producto->id_categoria) == $categoria->id_categoria)>{{ $categoria->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="id_proveedor" class="mb-1.5 block text-sm font-medium text-slate-700">Proveedor</label>
                <select id="id_proveedor" name="id_proveedor" class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Sin proveedor</option>
                    @foreach ($proveedores as $proveedor)
                        <option value="{{ $proveedor->id_proveedor }}" @selected(old('id_proveedor', $producto->id_proveedor) == $proveedor->id_proveedor)>{{ $proveedor->razon_social }}</option>
                    @endforeach
                </select>
                @error('id_proveedor')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-button variant="secondary" href="{{ route('productos.index') }}">Cancelar</x-button>
                <x-button type="submit">Actualizar producto</x-button>
            </div>
        </form>
    </x-card>
@endsection
