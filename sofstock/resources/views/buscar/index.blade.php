@extends('layouts.app')

@section('title', 'Buscar')
@section('page-title', 'Buscar')

@section('content')
    @if ($query === '')
        <p class="text-sm text-slate-500">Escribe en la búsqueda de arriba para encontrar módulos y registros.</p>
    @elseif ($results->isEmpty())
        <x-card>
            <p class="py-4 text-center text-sm text-slate-500">No se encontraron resultados para “{{ $query }}”.</p>
        </x-card>
    @else
        <div class="space-y-3">
            @foreach ($results as $result)
                <a href="{{ $result->url }}" class="block rounded-xl border border-slate-200 bg-white px-5 py-4 shadow-sm transition hover:border-primary-300 hover:bg-slate-50">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <h2 class="font-semibold text-slate-800">{{ $result->titulo }}</h2>
                            @if ($result->detalle)
                                <p class="mt-1 text-sm text-slate-500">{{ $result->detalle }}</p>
                            @endif
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">{{ $result->tipo }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
@endsection
