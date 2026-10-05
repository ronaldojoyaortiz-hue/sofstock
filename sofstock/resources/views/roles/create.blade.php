@extends('layouts.app')

@section('title', 'Nuevo rol')

@section('page-title', 'Nuevo rol')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Nuevo rol</h1>
        <p class="mt-1 text-sm text-slate-500">Crea un rol para asignar permisos a los usuarios.</p>
    </div>

    <x-card class="max-w-2xl">
        <form action="{{ route('roles.store') }}" method="POST" class="space-y-5">
            @csrf

            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    <p class="font-semibold">No se pudo guardar el rol:</p>
                    <ul class="mt-2 list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <x-input name="nombre_rol" label="Nombre" :value="old('nombre_rol')" required />

            <div>
                <label for="descripcion" class="mb-1.5 block text-sm font-medium text-slate-700">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="3" class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm">{{ old('descripcion') }}</textarea>
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-button variant="secondary" href="{{ route('roles.index') }}">Cancelar</x-button>
                <x-button type="submit">Guardar rol</x-button>
            </div>
        </form>
    </x-card>
@endsection
