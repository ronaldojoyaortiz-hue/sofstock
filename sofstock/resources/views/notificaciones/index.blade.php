@extends('layouts.app')

@section('title', 'Notificaciones')
@section('page-title', 'Notificaciones')

@section('content')
    @php
        $usuario = auth()->user();
        $esAdministrador = $usuario->hasRole('administrador')
            || mb_strtolower(trim($usuario->rol?->nombre_rol ?? '')) === 'administrador';
        $puedeEliminarNotificaciones = $esAdministrador || $usuario->can('eliminar notificaciones');
    @endphp

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Mis notificaciones</h1>
            <p class="mt-1 text-sm text-slate-500">
                Tienes {{ $noLeidas }} {{ $noLeidas === 1 ? 'notificación sin leer' : 'notificaciones sin leer' }}.
            </p>
        </div>
        @if ($esAdministrador || $usuario->can('crear notificaciones'))
            <a href="{{ route('notificaciones.create') }}" class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                Crear notificación
            </a>
        @endif
    </div>

    @if ($notificaciones->isEmpty())
        <x-card>
            <p class="py-5 text-center text-sm text-slate-500">No tienes notificaciones por ahora.</p>
        </x-card>
    @else
        <div class="space-y-4">
            @foreach ($notificaciones as $notificacion)
                @php($leida = $notificacion->pivot->leida_at !== null)
                <article class="rounded-xl border {{ $leida ? 'border-slate-200 bg-white' : 'border-primary-200 bg-primary-50/40' }} p-5 shadow-sm">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <h2 class="font-semibold text-slate-800">{{ $notificacion->titulo }}</h2>
                                <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $notificacion->tipo === \App\Models\Notificacion::TIPO_STOCK_BAJO ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $notificacion->tipo === \App\Models\Notificacion::TIPO_STOCK_BAJO ? 'Stock bajo' : 'Aviso' }}
                                </span>
                                @unless ($leida)
                                    <span class="rounded-full bg-primary-100 px-2.5 py-1 text-xs font-medium text-primary-700">Nueva</span>
                                @endunless
                            </div>

                            <p class="whitespace-pre-line text-sm leading-6 text-slate-600">{{ $notificacion->mensaje }}</p>

                            @if ($notificacion->tipo === \App\Models\Notificacion::TIPO_SOLICITUD_REGISTRO && $esAdministrador)
                                <a href="{{ route('registration-requests.index') }}" class="mt-3 inline-flex text-sm font-semibold text-primary-700 hover:text-primary-900">
                                    Revisar solicitudes de registro
                                </a>
                            @endif

                            @if ($notificacion->fecha_evento)
                                <p class="mt-3 text-sm font-medium text-slate-600">
                                    Fecha del evento: {{ $notificacion->fecha_evento->format('d/m/Y H:i') }}
                                </p>
                            @endif

                            @if ($notificacion->lugar)
                                <p class="mt-2 text-sm font-medium text-slate-600">Lugar: {{ $notificacion->lugar }}</p>
                            @endif

                            @if ($notificacion->producto)
                                <p class="mt-2 text-sm text-slate-500">Producto: {{ $notificacion->producto->nombre }}</p>
                            @endif

                            <p class="mt-3 text-xs text-slate-400">
                                Enviada por
                                {{ $notificacion->emisor
                                    ? trim($notificacion->emisor->nombre . ' ' . $notificacion->emisor->apellido)
                                    : 'el sistema' }}
                                · {{ $notificacion->created_at?->diffForHumans() }}
                            </p>
                        </div>

                        <div class="flex shrink-0 flex-wrap gap-2">
                            @unless ($leida)
                                <form method="POST" action="{{ route('notificaciones.leida', $notificacion) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        Marcar como leída
                                    </button>
                                </form>
                            @endunless

                            @if ($puedeEliminarNotificaciones)
                                <form method="POST" action="{{ route('notificaciones.destroy', $notificacion) }}" onsubmit="return confirm('¿Deseas eliminar esta notificación? Se eliminará para todos sus destinatarios.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-red-200 bg-white px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                                        Eliminar
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $notificaciones->links() }}
        </div>
    @endif
@endsection
