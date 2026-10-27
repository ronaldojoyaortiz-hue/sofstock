@extends('layouts.app')

@section('title', 'Detalle del usuario')
@section('page-title', 'Detalle del usuario')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">
                {{ $usuario->nombre }} {{ $usuario->apellido }}
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Usuario #{{ $usuario->id }}
            </p>
        </div>

        <div class="flex gap-3">
            <x-button variant="secondary" href="{{ route('usuarios.index') }}">
                Volver a usuarios
            </x-button>

            <x-button href="{{ route('usuarios.edit', $usuario->id) }}">
                Editar usuario
            </x-button>
        </div>
    </div>

    {{-- Información básica --}}
    <x-card>
        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500">
                    Nombre
                </p>

                <p class="mt-1 text-lg font-semibold text-slate-800">
                    {{ $usuario->nombre }} {{ $usuario->apellido }}
                </p>
            </div>

            <div class="sm:text-right">
                <p class="text-xs uppercase tracking-wide text-slate-500">
                    Estado
                </p>

                <span class="mt-2 inline-flex rounded-full px-2.5 py-1 text-sm font-medium
                    {{ $usuario->activo ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                    {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                </span>
            </div>
        </div>
    </x-card>

    {{-- Datos del usuario --}}
    <x-card class="mt-6">
        <div class="grid gap-6 sm:grid-cols-2">

            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500">
                    Correo electrónico
                </p>

                <p class="mt-1 font-medium text-slate-800">
                    {{ $usuario->correo }}
                </p>
            </div>

            <div class="sm:text-right">
                <p class="text-xs uppercase tracking-wide text-slate-500">
                    Rol
                </p>

                <p class="mt-1 font-medium text-slate-800">
                    {{ $usuario->rol?->nombre_rol ?? 'Sin rol asignado' }}
                </p>
            </div>

            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500">
                    Teléfono
                </p>

                <p class="mt-1 font-medium text-slate-800">
                    {{ $usuario->telefono ?: 'No registrado' }}
                </p>
            </div>

            <div class="sm:text-right">
                <p class="text-xs uppercase tracking-wide text-slate-500">
                    Género
                </p>

                <p class="mt-1 font-medium text-slate-800">
                    {{ $usuario->genero ?: 'No registrado' }}
                    @if ($usuario->genero === 'Otro' && $usuario->genero_detalle)
                        <span class="block text-sm font-normal text-slate-500">{{ $usuario->genero_detalle }}</span>
                    @endif
                </p>
            </div>

            <div class="sm:text-right">
                <p class="text-xs uppercase tracking-wide text-slate-500">
                    Dirección
                </p>

                <p class="mt-1 font-medium text-slate-800">
                    {{ $usuario->direccion ?: 'No registrada' }}
                </p>
            </div>

            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500">
                    Tipo de documento
                </p>

                <p class="mt-1 font-medium text-slate-800">
                    {{ $usuario->tipo_documento ?: 'No registrado' }}
                </p>
            </div>

            <div class="sm:text-right">
                <p class="text-xs uppercase tracking-wide text-slate-500">
                    Número de documento
                </p>

                <p class="mt-1 font-medium text-slate-800">
                    {{ $usuario->documento ?: 'No registrado' }}
                </p>
            </div>

            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500">
                    Fecha de registro
                </p>

                <p class="mt-1 font-medium text-slate-800">
                    {{ $usuario->fecha_registro?->format('d/m/Y H:i') ?? 'No disponible' }}
                </p>
            </div>

            <div class="sm:text-right">
                <p class="text-xs uppercase tracking-wide text-slate-500">
                    Última actualización
                </p>

                <p class="mt-1 font-medium text-slate-800">
                    {{ $usuario->fecha_actualizacion?->format('d/m/Y H:i') ?? 'Sin actualizaciones' }}
                </p>
            </div>
        </div>

        {{-- Administración de contraseña --}}
        <div class="mt-6 border-t border-slate-200 pt-4">
            {{-- Mostrar contraseña actual --}}
            <div class="mt-4">
                <label for="contrasenaActual" class="text-sm font-medium text-slate-700">
                    Contraseña protegida
                </label>

                <div class="mt-2 flex items-center gap-2">
                    <input
                        id="contrasenaActual"
                        type="password"
                        value="{{ $contrasenaVisible ?? '' }}"
                        readonly
                        placeholder="Contraseña no disponible"
                        autocomplete="off"
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-slate-800"
                    >

                    <button
                        type="button"
                        onclick="
                            const campo = document.getElementById('contrasenaActual');
                            campo.type = campo.type === 'password' ? 'text' : 'password';
                        "
                        class="rounded-lg border border-slate-300 px-3 py-2 hover:bg-slate-100"
                        title="Mostrar u ocultar contraseña"
                        aria-label="Mostrar u ocultar contraseña"
                    >
                        👁
                    </button>
                </div>

                @if (empty($contrasenaVisible))
                    <p class="mt-2 text-xs text-slate-500">
                        No hay una copia cifrada disponible para mostrar esta contraseña.
                    </p>

                    <form
                        method="POST"
                        action="{{ route('usuarios.restablecer-contrasena', $usuario->id) }}"
                        class="mt-3"
                    >
                        @csrf
                        <x-button type="submit" variant="secondary">
                            Restablecer contraseña
                        </x-button>
                    </form>
                @endif
            </div>

            {{-- Mensaje de confirmación --}}
            @if (session('mensaje'))
                <p class="mt-4 text-sm font-medium text-emerald-700">
                    {{ session('mensaje') }}
                </p>
            @endif

            {{-- Mostrar contraseña temporal recién generada --}}
            @if (session('contrasena_temporal'))
                <div class="mt-4 rounded-lg border border-amber-300 bg-amber-50 p-4">
                    <p class="text-sm font-semibold text-amber-900">
                        Nueva contraseña temporal
                    </p>

                    <div class="mt-2 flex items-center gap-2">
                        <input
                            id="contrasenaTemporal"
                            type="password"
                            value="{{ session('contrasena_temporal') }}"
                            readonly
                            autocomplete="off"
                            class="w-full rounded-lg border border-amber-300 bg-white px-3 py-2 text-slate-800"
                        >

                        <button
                            type="button"
                            onclick="
                                const campo = document.getElementById('contrasenaTemporal');
                                campo.type = campo.type === 'password' ? 'text' : 'password';
                            "
                            class="rounded-lg border border-amber-300 bg-white px-3 py-2 hover:bg-amber-100"
                            title="Mostrar u ocultar contraseña"
                            aria-label="Mostrar u ocultar contraseña"
                        >
                            👁
                        </button>

                        <button
                            type="button"
                            onclick="
                                const campo = document.getElementById('contrasenaTemporal');
                                navigator.clipboard.writeText(campo.value)
                                    .then(() => alert('Contraseña copiada'))
                                    .catch(() => alert('No se pudo copiar. Selecciona la contraseña manualmente.'));
                            "
                            class="rounded-lg border border-amber-300 bg-white px-3 py-2 hover:bg-amber-100"
                        >
                            Copiar
                        </button>
                    </div>

                    <p class="mt-2 text-xs text-amber-800">
                        Guarda esta contraseña y entrégasela al usuario de forma segura.
                    </p>
                </div>
            @endif
        </div>
    </x-card>
@endsection
