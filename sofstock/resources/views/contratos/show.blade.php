@extends('layouts.app')

@section('title', 'Detalle del contrato')
@section('page-title', 'Detalle del contrato')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-slate-500">Inmueble {{ $inmobiliario->codigo }} · {{ $inmobiliario->direccion }}, {{ $inmobiliario->ciudad }}</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-800">Contrato {{ $contrato->numero_contrato }}</h1>
            <p class="mt-1 text-sm text-slate-500">ID interno: {{ $contrato->id_contrato }}</p>
        </div>
        <div class="flex gap-3">
            <x-button variant="secondary" href="{{ route('inmuebles.contratos.index', $inmobiliario->id_inmueble) }}">Volver a contratos</x-button>
            <x-button href="{{ route('inmuebles.contratos.edit', [$inmobiliario->id_inmueble, $contrato->id_contrato]) }}">Editar contrato</x-button>
        </div>
    </div>

    <x-card>
        <dl class="grid gap-x-8 gap-y-6 sm:grid-cols-2">
            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tipo</dt><dd class="mt-1 text-slate-800">{{ $contrato->tipo_contrato }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Estado</dt><dd class="mt-1 text-slate-800">{{ $contrato->estado }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Contraparte</dt><dd class="mt-1 text-slate-800">{{ $contrato->contraparte }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Documento</dt><dd class="mt-1 text-slate-800">{{ $contrato->documento_contraparte ?: 'No especificado' }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Fecha de inicio</dt><dd class="mt-1 text-slate-800">{{ $contrato->fecha_inicio->format('d/m/Y') }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Fecha de finalización</dt><dd class="mt-1 text-slate-800">{{ $contrato->fecha_fin?->format('d/m/Y') ?? 'Sin fecha de finalización' }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Valor</dt><dd class="mt-1 text-slate-800">{{ $contrato->valor !== null ? '$' . number_format((float) $contrato->valor, 2, ',', '.') : 'No especificado' }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Observaciones</dt><dd class="mt-1 whitespace-pre-line text-slate-800">{{ $contrato->observaciones ?: 'Sin observaciones.' }}</dd></div>
        </dl>
    </x-card>

    @if ($contrato->archivos)
        <x-card class="mt-6">
            <h2 class="text-xl font-bold text-slate-800">Fotos y documentos adjuntos</h2>
            <ul class="mt-4 divide-y divide-slate-100">
                @foreach ($contrato->archivos as $indice => $archivo)
                    <li class="py-3 first:pt-0">
                        <a class="text-primary-600 hover:text-primary-800" href="{{ route('inmuebles.contratos.archivos.show', [$inmobiliario->id_inmueble, $contrato->id_contrato, $indice]) }}" target="_blank" rel="noopener">
                            {{ $archivo['name'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </x-card>
    @endif
@endsection
