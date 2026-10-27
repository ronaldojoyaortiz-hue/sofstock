@extends('layouts.app')

@section('title', 'Nuevo contrato')
@section('page-title', 'Nuevo contrato')

@section('content')
    <div class="mb-6">
        <p class="text-sm text-slate-500">Inmueble {{ $inmobiliario->codigo }} · {{ $inmobiliario->direccion }}, {{ $inmobiliario->ciudad }}</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-800">Registrar contrato</h1>
    </div>
    <x-card class="max-w-4xl">
        @include('contratos._form')
    </x-card>
@endsection
