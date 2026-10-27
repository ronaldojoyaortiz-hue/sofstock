@extends('layouts.app')

@section('title', 'Seleccionar inmueble')
@section('page-title', 'Registrar contrato')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Registrar contrato</h1>
        <p class="mt-1 text-sm text-slate-500">Primero selecciona el inmueble al que pertenece el contrato.</p>
    </div>

    <x-card class="max-w-2xl">
        @if ($inmuebles->isEmpty())
            <p class="text-sm text-slate-600">
                No hay inmuebles registrados. Registra un inmueble antes de crear un contrato.
            </p>
            <div class="mt-5">
                <x-button href="{{ route('inmuebles.create') }}">Registrar inmueble</x-button>
            </div>
        @else
            <form action="{{ route('contratos.select-inmueble') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="id_inmueble" class="mb-1.5 block text-sm font-medium text-slate-700">
                        Inmueble
                    </label>
                    <select
                        id="id_inmueble"
                        name="id_inmueble"
                        required
                        class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                    >
                        <option value="">Selecciona un inmueble</option>
                        @foreach ($inmuebles as $inmueble)
                            <option value="{{ $inmueble->id_inmueble }}" @selected(old('id_inmueble') == $inmueble->id_inmueble)>
                                {{ $inmueble->codigo }} · {{ $inmueble->direccion }}, {{ $inmueble->ciudad }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_inmueble')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <x-button variant="secondary" href="{{ route('contratos.index') }}">Cancelar</x-button>
                    <x-button type="submit">Continuar</x-button>
                </div>
            </form>
        @endif
    </x-card>
@endsection
