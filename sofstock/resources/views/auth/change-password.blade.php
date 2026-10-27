@extends('layouts.app')

@section('title', 'Cambiar contraseña')
@section('page-title', 'Cambiar contraseña')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Cambiar contraseña</h1>
        <p class="mt-1 text-sm text-slate-500">Confirma tu contraseña actual y escribe una nueva.</p>
    </div>

    <x-card class="max-w-2xl">
        <form action="{{ route('password.update') }}" method="POST" class="space-y-5">
            @csrf

            <x-password-input
                name="contrasena_actual"
                label="Contraseña actual"
                autocomplete="current-password"
                required
            />

            <x-password-input
                name="contrasena_nueva"
                label="Nueva contraseña"
                hint="Debe tener al menos 8 caracteres."
                required
            />
            <x-password-input
                name="contrasena_nueva_confirmation"
                label="Confirmar nueva contraseña"
                required
            />

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-button variant="secondary" href="{{ route('dashboard.index') }}">Cancelar</x-button>
                <x-button type="submit">Guardar contraseña</x-button>
            </div>
        </form>
    </x-card>
@endsection
