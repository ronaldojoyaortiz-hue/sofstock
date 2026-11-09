@extends('layouts.app')

@section('title', 'Solicitudes de registro')
@section('page-title', 'Solicitudes de registro')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Solicitudes de registro</h1>
        <p class="mt-1 text-sm text-slate-500">Aprueba o rechaza las cuentas nuevas. Al aprobar, asígnales un rol distinto de administrador.</p>
    </div>

    @if (session('success'))
        <div class="mb-5 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    @if ($solicitudes->isEmpty())
        <x-card>
            <p class="py-5 text-center text-sm text-slate-500">No hay solicitudes pendientes.</p>
        </x-card>
    @else
        <div class="space-y-4">
            @foreach ($solicitudes as $solicitud)
                <x-card>
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="font-semibold text-slate-800">{{ $solicitud->nombre }} {{ $solicitud->apellido }}</h2>
                            <p class="mt-1 text-sm text-slate-600">{{ $solicitud->correo }}</p>
                            <p class="mt-1 text-xs text-slate-400">Solicitada {{ $solicitud->fecha_registro?->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="flex flex-col gap-3 sm:min-w-72">
                            @if ($roles->isNotEmpty())
                                <form method="POST" action="{{ route('registration-requests.approve', $solicitud) }}" class="flex flex-col gap-2 sm:flex-row">
                                    @csrf
                                    <select name="id_rol" required class="min-w-0 flex-1 rounded-lg border border-slate-300 p-2 text-sm">
                                        <option value="">Selecciona un rol</option>
                                        @foreach ($roles as $rol)
                                            <option value="{{ $rol->id_rol }}">{{ $rol->nombre_rol }}</option>
                                        @endforeach
                                    </select>
                                    <button class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">Aprobar</button>
                                </form>
                            @else
                                <p class="text-sm text-amber-700">Crea primero un rol distinto de administrador para aprobar esta cuenta.</p>
                            @endif
                            <form method="POST" action="{{ route('registration-requests.reject', $solicitud) }}" onsubmit="return confirm('¿Rechazar esta solicitud?')">
                                @csrf
                                <button class="rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Rechazar</button>
                            </form>
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>
        <div class="mt-6">{{ $solicitudes->links() }}</div>
    @endif
@endsection
