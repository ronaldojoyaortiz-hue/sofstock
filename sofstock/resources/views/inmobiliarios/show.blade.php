@extends('layouts.app')

@section('title', 'Detalle del inmueble')
@section('page-title', 'Detalle del inmueble')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-medium text-slate-500">Código {{ $inmobiliario->codigo }}</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-800">{{ $inmobiliario->tipo_inmueble }}</h1>
        </div>
        <div class="flex flex-wrap gap-3">
            <x-button variant="secondary" href="{{ route('inmuebles.index') }}">Volver al listado</x-button>
            <x-button href="{{ route('inmuebles.edit', $inmobiliario->id_inmueble) }}">Editar inmueble</x-button>
        </div>
    </div>

    <x-card>
        <div class="grid gap-x-8 gap-y-6 sm:grid-cols-2">
            <div><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tipo de inmueble</p><p class="mt-1 text-slate-800">{{ $inmobiliario->tipo_inmueble }}</p></div>
            <div><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Estado</p><p class="mt-1 text-slate-800">{{ $inmobiliario->estado }}</p></div>
            <div><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Dirección</p><p class="mt-1 text-slate-800">{{ $inmobiliario->direccion }}</p></div>
            <div><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Ciudad</p><p class="mt-1 text-slate-800">{{ $inmobiliario->ciudad }}</p></div>
            <div><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Área</p><p class="mt-1 text-slate-800">{{ $inmobiliario->area_m2 !== null ? number_format((float) $inmobiliario->area_m2, 2, ',', '.') . ' m²' : 'No especificada' }}</p></div>
            <div class="sm:col-span-2"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Descripción</p><p class="mt-1 whitespace-pre-line text-slate-800">{{ $inmobiliario->descripcion ?: 'Sin descripción.' }}</p></div>
            <div class="sm:col-span-2"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Observaciones</p><p class="mt-1 whitespace-pre-line text-slate-800">{{ $inmobiliario->observaciones ?: 'Sin observaciones.' }}</p></div>
        </div>
    </x-card>

    @if ($inmobiliario->archivos)
        <x-card class="mt-6">
            <h2 class="text-xl font-bold text-slate-800">Archivos del inmueble</h2>
            <ul class="mt-4 divide-y divide-slate-100">
                @foreach ($inmobiliario->archivos as $indice => $archivo)
                    <li class="py-3 first:pt-0">
                        <a class="text-primary-600 hover:text-primary-800" href="{{ route('inmuebles.archivos.show', [$inmobiliario->id_inmueble, $indice]) }}" target="_blank" rel="noopener">
                            {{ $archivo['name'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </x-card>
    @endif

@endsection
