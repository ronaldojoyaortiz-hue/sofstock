@extends('layouts.app')

@section('title', 'Editar usuario')
@section('page-title', 'Editar usuario')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Editar usuario</h1>
        <p class="mt-1 text-sm text-slate-500">Actualiza los datos del usuario.</p>
    </div>

    <x-card class="max-w-2xl">
        <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <x-input name="nombre" label="Nombre" :value="old('nombre', $usuario->nombre)" required />
            <x-input name="apellido" label="Apellido" :value="old('apellido', $usuario->apellido)" required />
            <x-input name="correo" label="Correo electrónico" type="email" :value="old('correo', $usuario->correo)" required />
            <x-input name="telefono" label="Teléfono" type="tel" :value="old('telefono', $usuario->telefono)" />
            <x-input name="direccion" label="Dirección" :value="old('direccion', $usuario->direccion)" />
            <div x-data="{ genero: @js(old('genero', $usuario->genero ?? '')) }" class="space-y-5">
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
                            <option value="{{ $genero }}" @selected(old('genero', $usuario->genero) === $genero)>{{ $genero }}</option>
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
                        value="{{ old('genero_detalle', $usuario->genero_detalle) }}"
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
                        <option value="{{ $tipoDocumento }}" @selected(old('tipo_documento', $usuario->tipo_documento) === $tipoDocumento)>{{ $tipoDocumento }}</option>
                    @endforeach
                </select>
                @error('tipo_documento')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <x-input name="documento" label="Número de documento" :value="old('documento', $usuario->documento)" />
            <x-password-input name="contrasena" label="Nueva contraseña" hint="Déjala vacía para conservar la contraseña actual." />
            <x-password-input name="contrasena_confirmation" label="Confirmar nueva contraseña" />

            <div>
                <label for="id_rol" class="mb-1.5 block text-sm font-medium text-slate-700">Rol</label>
                <select id="id_rol" name="id_rol" required class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm">
                    <option value="">Selecciona un rol</option>
                    @foreach ($roles as $rol)
                        <option value="{{ $rol->id_rol }}" @selected(old('id_rol', $usuario->id_rol) == $rol->id_rol)>{{ $rol->nombre_rol }}</option>
                    @endforeach
                </select>
                @error('id_rol')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="activo" value="1" @checked(old('activo', $usuario->activo) == '1') class="rounded border-slate-300">
                Usuario activo
            </label>
            @error('activo')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-button variant="secondary" href="{{ route('usuarios.index') }}">Cancelar</x-button>
                <x-button type="submit">Guardar cambios</x-button>
            </div>
        </form>
    </x-card>
@endsection
