@extends('layouts.app')

@section('title', 'Nuevo usuario')
@section('page-title', 'Nuevo usuario')

@section('content')
    <div class="mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Nuevo usuario</h1>
            <p class="mt-1 text-sm text-slate-500">Completa los datos para registrar un usuario.</p>
        </div>
    </div>

    <x-card class="max-w-2xl">
        <form action="{{ route('usuarios.store') }}" method="POST" class="space-y-5">
            @csrf

            <x-input name="nombre" label="Nombre" :value="old('nombre')" required />
            <x-input name="apellido" label="Apellido" :value="old('apellido')" required />
            <x-input name="correo" label="Correo electrónico" type="email" :value="old('correo')" required />
            <x-input name="contrasena" label="Contraseña" type="password" required />
            <x-input name="contrasena_confirmation" label="Confirmar contraseña" type="password" required />

            <div>
                <label for="id_rol" class="mb-1.5 block text-sm font-medium text-slate-700">Rol</label>
                <select id="id_rol" name="id_rol" required class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm">
                    <option value="">Selecciona un rol</option>
                    @foreach ($roles as $rol)
                        <option value="{{ $rol->id_rol }}" @selected(old('id_rol') == $rol->id_rol)>{{ $rol->nombre_rol }}</option>
                    @endforeach
                </select>
                @error('id_rol')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="activo" value="1" @checked(old('activo', '1') == '1') class="rounded border-slate-300">
                Usuario activo
            </label>
            @error('activo')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-button variant="secondary" href="{{ route('usuarios.index') }}">Cancelar</x-button>
                <x-button type="submit">Guardar usuario</x-button>
            </div>
        </form>
    </x-card>
@endsection