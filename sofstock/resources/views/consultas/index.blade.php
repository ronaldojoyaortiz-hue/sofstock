@extends('layouts.app')

@section('title', 'Consultas')
@section('page-title', 'Consultas')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Consultas</h1>
        <p class="mt-1 text-sm text-slate-500">Selecciona el módulo y después el criterio para buscar la información.</p>
    </div>

    <form method="GET" action="{{ route('consultas.index') }}" class="max-w-2xl rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <label for="modulo" class="mb-1 block text-sm font-medium text-slate-700">Selecciona qué quieres consultar</label>
        <select id="modulo" name="modulo" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
            <option value="" selected disabled>Selecciona un módulo</option>
            @foreach ($modulos as $modulo)
                <option value="{{ $modulo['tipo'] }}">{{ $modulo['titulo'] }}</option>
            @endforeach
        </select>
        <p class="mt-2 text-sm text-slate-500">Luego podrás elegir un criterio, como fecha, y consultar los registros de ese módulo.</p>
        <div class="mt-4 flex justify-end">
            <button type="submit" class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">Continuar</button>
        </div>
    </form>
@endsection
