@extends('layouts.app')

@section('title', $detalle['titulo'])
@section('page-title', $detalle['titulo'])

@section('content')
    <div class="mb-6">
        <a href="{{ route("consultas.{$tipoConsulta}.index", request()->query()) }}" class="text-sm font-medium text-primary-700 hover:text-primary-900">← Volver a {{ mb_strtolower($modulo['titulo']) }}</a>
        <h1 class="mt-2 text-2xl font-bold text-slate-800">{{ $detalle['titulo'] }}</h1>
    </div>

    <div class="space-y-6">
        @foreach ($detalle['secciones'] as $seccion)
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="font-semibold text-slate-800">{{ $seccion['titulo'] }}</h2>
                </div>

                @if (isset($seccion['campos']))
                    <dl class="grid grid-cols-1 gap-x-6 gap-y-5 p-5 sm:grid-cols-2 xl:grid-cols-3">
                        @forelse ($seccion['campos'] as $campo)
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $campo['etiqueta'] }}</dt>
                                <dd class="mt-1 whitespace-pre-wrap break-words text-sm text-slate-800">{{ $campo['valor'] ?: 'No registrado' }}</dd>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">No hay información adicional registrada.</p>
                        @endforelse
                    </dl>
                @endif

                @if (isset($seccion['columnas'], $seccion['filas']))
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <tr>
                                    @foreach ($seccion['columnas'] as $columna)
                                        <th scope="col" class="whitespace-nowrap px-5 py-3">{{ $columna }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-600">
                                @forelse ($seccion['filas'] as $fila)
                                    <tr>
                                        @foreach ($fila as $valor)
                                            <td class="whitespace-pre-wrap px-5 py-4">{{ $valor ?: '—' }}</td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr><td colspan="{{ count($seccion['columnas']) }}" class="px-5 py-8 text-center text-slate-500">No hay registros relacionados.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        @endforeach
    </div>
@endsection
