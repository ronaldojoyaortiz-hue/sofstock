@extends('layouts.app')

@section('title', 'Editar reporte')
@section('page-title', 'Editar reporte')

@section('content')
    <div class="mb-6">
        <a href="{{ route('reports.index') }}" class="text-sm font-medium text-primary-700 hover:text-primary-900">← Volver a reportes</a>
        <h1 class="mt-3 text-2xl font-bold text-slate-800">Editar reporte #{{ $reporte->id }}</h1>
        <p class="mt-1 text-sm text-slate-500">El reporte se actualizará con los datos del período seleccionado.</p>
    </div>

    <x-card class="max-w-2xl">
        <form x-data="{ periodo: @js(old('periodo', $reporte->tipo_reporte)) }" method="POST" action="{{ route('reports.update', $reporte) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="periodo" class="mb-1 block text-sm font-medium text-slate-700">Tipo de reporte</label>
                <select id="periodo" name="periodo" x-model="periodo" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
                    <option value="diario">Diario</option>
                    <option value="semanal">Semanal</option>
                    <option value="mensual">Mensual</option>
                    <option value="anual">Anual</option>
                    <option value="personalizado">Rango personalizado</option>
                </select>
            </div>
            <div x-show="['diario', 'semanal'].includes(periodo)">
                <label for="referencia" class="mb-1 block text-sm font-medium text-slate-700">Fecha de referencia</label>
                <input id="referencia" name="referencia" type="date" max="{{ now()->toDateString() }}" :required="['diario', 'semanal'].includes(periodo)" value="{{ old('referencia', $referencia) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
            </div>
            <div x-show="periodo === 'mensual'" x-cloak>
                <label for="mes" class="mb-1 block text-sm font-medium text-slate-700">Mes a consultar</label>
                <input id="mes" name="mes" type="month" min="2000-01" max="{{ now()->format('Y-m') }}" :required="periodo === 'mensual'" value="{{ old('mes', $reporte->fecha_desde->format('Y-m')) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
            </div>
            <div x-show="periodo === 'anual'" x-cloak>
                <label for="anio" class="mb-1 block text-sm font-medium text-slate-700">Año a consultar</label>
                <input id="anio" name="anio" type="number" min="2000" max="{{ now()->year }}" :required="periodo === 'anual'" value="{{ old('anio', $reporte->fecha_desde->format('Y')) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
            </div>
            <div x-show="periodo === 'personalizado'" x-cloak class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="fecha_desde" class="mb-1 block text-sm font-medium text-slate-700">Desde</label>
                    <input id="fecha_desde" name="fecha_desde" type="date" :required="periodo === 'personalizado'" value="{{ old('fecha_desde', $reporte->fecha_desde->toDateString()) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
                </div>
                <div>
                    <label for="fecha_hasta" class="mb-1 block text-sm font-medium text-slate-700">Hasta</label>
                    <input id="fecha_hasta" name="fecha_hasta" type="date" :required="periodo === 'personalizado'" value="{{ old('fecha_hasta', $reporte->fecha_hasta->toDateString()) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <x-button variant="secondary" href="{{ route('reports.index') }}">Cancelar</x-button>
                <x-button type="submit">Actualizar reporte</x-button>
            </div>
        </form>
    </x-card>
@endsection
