@extends('layouts.app')

@section('title', 'Mi cuenta')
@section('page-title', 'Mi cuenta')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">
            {{ $usuario->nombre }} {{ $usuario->apellido }}
        </h1>
        <p class="mt-1 text-sm text-slate-500">{{ $usuario->correo }}</p>
    </div>

    <x-card class="mb-6 max-w-3xl" title="Seguridad de la cuenta">
        <p class="mb-4 text-sm text-slate-600">Actualiza tu contraseña para proteger el acceso a SofStock.</p>
        <a href="{{ route('password.change') }}" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary-500">
            Cambiar contraseña
        </a>
    </x-card>

    @include('usuario.partials.deactivate-account')
@endsection
