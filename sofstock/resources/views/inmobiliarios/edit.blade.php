@extends('layouts.app')

@section('title', 'Editar inmueble')
@section('page-title', 'Editar inmueble')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Editar inmueble</h1>
        <p class="mt-1 text-sm text-slate-500">Actualiza los datos del inmueble {{ $inmobiliario->codigo }}.</p>
    </div>

    <x-card class="max-w-4xl">
        <form action="{{ route('inmuebles.update', $inmobiliario->id_inmueble) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    <p class="font-semibold">Revisa los datos del inmueble:</p>
                    <ul class="mt-2 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="codigo" class="mb-1.5 block text-sm font-medium text-slate-700">Código</label>
                    <input id="codigo" name="codigo" value="{{ old('codigo', $inmobiliario->codigo) }}" maxlength="30" required class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label for="tipo_inmueble" class="mb-1.5 block text-sm font-medium text-slate-700">Tipo de inmueble</label>
                    <input id="tipo_inmueble" name="tipo_inmueble" value="{{ old('tipo_inmueble', $inmobiliario->tipo_inmueble) }}" maxlength="50" required class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label for="area_m2" class="mb-1.5 block text-sm font-medium text-slate-700">Área (m²)</label>
                    <input id="area_m2" name="area_m2" type="number" min="0" step="0.01" value="{{ old('area_m2', $inmobiliario->area_m2) }}" class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div class="sm:col-span-2">
                    <label for="direccion" class="mb-1.5 block text-sm font-medium text-slate-700">Dirección</label>
                    <input id="direccion" name="direccion" value="{{ old('direccion', $inmobiliario->direccion) }}" maxlength="255" required class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label for="ciudad" class="mb-1.5 block text-sm font-medium text-slate-700">Ciudad</label>
                    <input id="ciudad" name="ciudad" value="{{ old('ciudad', $inmobiliario->ciudad) }}" maxlength="100" required class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label for="estado" class="mb-1.5 block text-sm font-medium text-slate-700">Estado</label>
                    <select id="estado" name="estado" required class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-primary-500">
                        @foreach (['Disponible', 'Ocupado', 'Mantenimiento', 'Inactivo'] as $estado)
                            <option value="{{ $estado }}" @selected(old('estado', $inmobiliario->estado) === $estado)>{{ $estado }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label for="descripcion" class="mb-1.5 block text-sm font-medium text-slate-700">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="3" class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-primary-500">{{ old('descripcion', $inmobiliario->descripcion) }}</textarea>
                </div>
                <div class="sm:col-span-2">
                    <label for="observaciones" class="mb-1.5 block text-sm font-medium text-slate-700">Observaciones</label>
                    <textarea id="observaciones" name="observaciones" rows="3" class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-primary-500">{{ old('observaciones', $inmobiliario->observaciones) }}</textarea>
                </div>
                <div class="sm:col-span-2">
                    <label for="archivos" class="mb-1.5 mt-4 block text-sm font-medium text-slate-700">Agregar documentos al inmueble</label>
                    <input id="archivos" name="archivos[]" type="file" accept=".pdf,.doc,.docx,.xls,.xlsx" multiple class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm">
                    <p class="mt-1 text-xs text-slate-500">PDF, Word, Excel o imágenes; máximo 10 MB cada uno. Los documentos actuales se conservarán.</p>
                    @if ($inmobiliario->archivos)
                        <ul class="mt-3 space-y-1 text-sm">
                            @foreach ($inmobiliario->archivos as $indice => $archivo)
                                <li><a class="text-primary-600 hover:text-primary-800" href="{{ route('inmuebles.archivos.show', [$inmobiliario->id_inmueble, $indice]) }}" target="_blank" rel="noopener">{{ $archivo['name'] }}</a></li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                <x-button variant="secondary" href="{{ route('inmuebles.index') }}">Cancelar</x-button>
                <x-button type="submit">Guardar cambios</x-button>
            </div>
        </form>
    </x-card>
@endsection
