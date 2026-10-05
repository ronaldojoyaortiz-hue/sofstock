@extends('layouts.app')

@section('title', 'Nuevo detalle de factura')
@section('page-title', 'Nuevo detalle de factura')

@section('content')
    <h1 class="mb-6 text-2xl font-bold text-slate-800">Nuevo detalle de factura</h1>
    <x-card>
        <form action="{{ route('detallefacturas.store') }}" method="POST" class="space-y-6">
            @csrf
            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Factura</label>
                    <select name="id_factura" class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm">
                        <option value="">Seleccione una factura</option>
                        @foreach ($facturas as $factura)
                            <option value="{{ $factura->id_factura }}" @selected(old('id_factura') == $factura->id_factura)>
                                #{{ $factura->id_factura }} - {{ $factura->cliente_nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Producto</label>
                    <select name="id_producto" class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm">
                        <option value="">Seleccione un producto</option>
                        @foreach ($productos as $producto)
                            <option value="{{ $producto->id }}" @selected(old('id_producto') == $producto->id)>
                                {{ $producto->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Nombre del producto</label>
                    <input name="producto_nombre" value="{{ old('producto_nombre') }}" class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm" placeholder="Nombre congelado" />
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Cantidad</label>
                    <input name="cantidad" type="number" min="0.01" step="0.01" value="{{ old('cantidad', 1) }}" class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm" />
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Precio unitario</label>
                    <input name="precio_unitario" type="number" min="0" step="0.01" value="{{ old('precio_unitario') }}" class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm" />
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Impuesto %</label>
                    <input name="porcentaje_impuesto" type="number" min="0" max="100" step="0.01" value="{{ old('porcentaje_impuesto', 0) }}" class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm" />
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Descuento</label>
                    <input name="descuento" type="number" min="0" step="0.01" value="{{ old('descuento', 0) }}" class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm" />
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <x-button variant="secondary" href="{{ route('detallefacturas.index') }}">Cancelar</x-button>
                <x-button type="submit">Guardar detalle</x-button>
            </div>
        </form>
    </x-card>
@endsection