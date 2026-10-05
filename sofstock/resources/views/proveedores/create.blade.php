@extends('layouts.app')

@section('title', 'Nuevo proveedor')
@section('page-title', 'Nuevo proveedor')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Nuevo proveedor</h1>
        <p class="mt-1 text-sm text-slate-500">Crea un proveedor para agregarlo a tu inventario.</p>
    </div>

    <x-card class="max-w-2xl">
        <form action="{{ route('proveedores.store') }}" method="POST" class="space-y-5">
            @csrf

            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    <p class="font-semibold">No se pudo guardar el proveedor:</p>
                    <ul class="mt-2 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
                 
            <x-input name="razon_social" label="Razón social" :value="old('razon_social')" required />
            <x-input name="nit" label="NIT" :value="old('nit')" />
            <x-input name="correo" label="Correo" type="email" :value="old('correo')" />
            <x-input name="telefono" label="Teléfono" :value="old('telefono')" />
            <x-input name="direccion" label="Dirección" :value="old('direccion')" />

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-button variant="secondary" href="{{ route('proveedores.index') }}">Cancelar</x-button>
                <x-button type="submit">Guardar proveedor</x-button>
            </div>
        </form>
    </x-card>
@endsection