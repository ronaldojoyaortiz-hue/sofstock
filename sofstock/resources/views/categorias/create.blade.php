@extends('layouts.app')

@section('title', 'Nueva categoría')
@section('page-title', 'Nueva categoría')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Nueva categoría</h1>
        <p class="mt-1 text-sm text-slate-500">Crea una categoría para organizar los productos.</p>
    </div>

    <x-card class="max-w-2xl">
        <form action="{{ route('categorias.store') }}" method="POST" class="space-y-5">
            @csrf

            <x-input name="nombre" label="Nombre" :value="old('nombre')" required />

            <div>
                <label for="descripcion" class="mb-1.5 block text-sm font-medium text-slate-700">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="4" class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-primary-500">{{ old('descripcion') }}</textarea>
            </div>

            <div>
                <label for="activa" class="mb-1.5 block text-sm font-medium text-slate-700">Estado</label>
                <select id="activa" name="activa" class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="1" @selected(old('activa', '1') == '1')>Activa</option>
                    <option value="0" @selected(old('activa') === '0')>Inactiva</option>
                </select>
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-button variant="secondary" href="{{ route('categorias.index') }}">Cancelar</x-button>
                <x-button type="submit">Guardar categoría</x-button>
            </div>
        </form>
    </x-card>
@endsection
