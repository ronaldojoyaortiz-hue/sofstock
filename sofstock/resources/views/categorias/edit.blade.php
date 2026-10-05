@extends('layouts.app')

@section('title', 'Editar categoría')
@section('page-title', 'Editar categoría')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Editar categoría</h1>
        <p class="mt-1 text-sm text-slate-500">Actualiza la información de {{ $categoria->nombre }}.</p>
    </div>

    <x-card class="max-w-2xl">
        <form action="{{ route('categorias.update', $categoria->id_categoria) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <x-input name="nombre" label="Nombre" :value="old('nombre', $categoria->nombre)" required />

            <div>
                <label for="descripcion" class="mb-1.5 block text-sm font-medium text-slate-700">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="4" class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-primary-500">{{ old('descripcion', $categoria->descripcion) }}</textarea>
            </div>

            <div>
                <label for="activa" class="mb-1.5 block text-sm font-medium text-slate-700">Estado</label>
                <select id="activa" name="activa" class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="1" @selected(old('activa', $categoria->activa ? '1' : '0') == '1')>Activa</option>
                    <option value="0" @selected(old('activa', $categoria->activa ? '1' : '0') == '0')>Inactiva</option>
                </select>
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-button variant="secondary" href="{{ route('categorias.index') }}">Cancelar</x-button>
                <x-button type="submit">Guardar cambios</x-button>
            </div>
        </form>
    </x-card>
@endsection
