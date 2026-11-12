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
            <x-input name="marca" label="Marca" :value="old('marca', $producto->marca)" />
            <x-input name="precio_base" label="Precio" type="number" step="0.01" min="0" :value="old('precio_base', $producto->precio_base)" required />
            <x-input name="stock_actual" label="Stock" type="number" step="1" min="0" :value="old('stock_actual', $producto->stock_actual)" required />
            <x-input name="stock_minimo" label="Stock mínimo para alerta" type="number" step="1" min="0" :value="old('stock_minimo', $producto->stock_minimo ?? 0)" />
            <x-input name="fecha_caducidad" label="Fecha de caducidad" type="date" :value="old('fecha_caducidad', $producto->fecha_caducidad?->format('Y-m-d'))" />
            <div><label for="descripcion" class="mb-1.5 block text-sm font-medium text-slate-700">Descripción</label><textarea id="descripcion" name="descripcion" rows="3" class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm">{{ old('descripcion', $producto->descripcion) }}</textarea></div>

            @if (auth()->user()?->esAdministrador())
                @php($destinatariosSeleccionados = old('usuarios_alerta_stock', $producto->usuariosDestinatariosAlertaStock->modelKeys()))
                <div>
                    <label for="usuarios_alerta_stock" class="mb-1.5 block text-sm font-medium text-slate-700">Destinatarios de alerta de stock bajo</label>
                    <select id="usuarios_alerta_stock" name="usuarios_alerta_stock[]" multiple size="5" class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-primary-500">
                        @foreach ($usuariosAlertaStock as $usuario)
                            <option value="{{ $usuario->id }}" @selected(in_array($usuario->id, $destinatariosSeleccionados))>{{ trim($usuario->nombre.' '.$usuario->apellido) }} ({{ $usuario->correo }})</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-500">Puedes seleccionar varios usuarios. Usa Ctrl (Windows) o Cmd (Mac) para elegir más de uno.</p>
                    @error('usuarios_alerta_stock')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('usuarios_alerta_stock.*')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endif

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
