@extends('layouts.app')

@section('title', 'Editar proveedor')
@section('page-title', 'Editar proveedor')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Editar proveedor</h1>
        <p class="mt-1 text-sm text-slate-500">Actualiza la información de {{ $proveedor->razon_social }}.</p>
    </div>

    <x-card class="max-w-2xl">
        <form action="{{ route('proveedores.update', $proveedor->id_proveedor) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    <p class="font-semibold">No se pudo actualizar el proveedor:</p>
                    <ul class="mt-2 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <x-input name="razon_social" label="Razón social" :value="old('razon_social', $proveedor->razon_social)" required />
            <x-input name="nit" label="NIT" :value="old('nit', $proveedor->nit)" />
            <x-input name="contacto_nombre" label="Nombre de contacto" :value="old('contacto_nombre', $proveedor->contacto_nombre)" />
            <x-input name="correo" label="Correo" type="email" :value="old('correo', $proveedor->correo)" />
            <x-input name="telefono" label="Teléfono" :value="old('telefono', $proveedor->telefono)" />
            <x-input name="direccion" label="Dirección" :value="old('direccion', $proveedor->direccion)" />

            <div>
                <label for="activo" class="mb-1.5 block text-sm font-medium text-slate-700">Estado</label>
                <select id="activo" name="activo" class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="1" @selected(old('activo', $proveedor->activo ? '1' : '0') == '1')>Activo</option>
                    <option value="0" @selected(old('activo', $proveedor->activo ? '1' : '0') == '0')>Inactivo</option>
                </select>
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-button variant="secondary" href="{{ route('proveedores.index') }}">Cancelar</x-button>
                <x-button type="submit">Guardar cambios</x-button>
            </div>
        </form>
    </x-card>
@endsection