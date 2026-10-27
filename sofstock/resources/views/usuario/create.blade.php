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
            <x-input name="telefono" label="Teléfono" type="tel" :value="old('telefono')" />
            <x-input name="direccion" label="Dirección" :value="old('direccion')" />
            <div x-data="{ genero: @js(old('genero', '')) }" class="space-y-5">
                <div>
                    <label for="genero" class="mb-1.5 block text-sm font-medium text-slate-700">Género</label>
                    <select
                        id="genero"
                        name="genero"
                        x-model="genero"
                        class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm"
                    >
                        <option value="">Selecciona una opción</option>
                        @foreach ($generos as $genero)
                            <option value="{{ $genero }}" @selected(old('genero') === $genero)>{{ $genero }}</option>
                        @endforeach
                    </select>
                    @error('genero')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div x-show="genero === 'Otro'" x-cloak>
                    <label for="genero_detalle" class="mb-1.5 block text-sm font-medium text-slate-700">
                        ¿Cómo te identificas? (opcional)
                    </label>
                    <input
                        id="genero_detalle"
                        name="genero_detalle"
                        type="text"
                        value="{{ old('genero_detalle') }}"
                        maxlength="100"
                        placeholder="Escribe aquí si deseas especificarlo"
                        x-bind:disabled="genero !== 'Otro'"
                        class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-primary-500"
                    >
                    @error('genero_detalle')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div>
                <label for="tipo_documento" class="mb-1.5 block text-sm font-medium text-slate-700">Tipo de documento</label>
                <select id="tipo_documento" name="tipo_documento" class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm">
                    <option value="">Selecciona un tipo de documento</option>
                    @foreach ($tiposDocumento as $tipoDocumento)
                        <option value="{{ $tipoDocumento }}" @selected(old('tipo_documento') === $tipoDocumento)>{{ $tipoDocumento }}</option>
                    @endforeach
                </select>
                @error('tipo_documento')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <x-input name="documento" label="Número de documento" :value="old('documento')" />
            <x-password-input name="contrasena" label="Contraseña" required />
            <x-password-input name="contrasena_confirmation" label="Confirmar contraseña" required />

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