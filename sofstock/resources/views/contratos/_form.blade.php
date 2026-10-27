@php($editando = isset($contrato))

<form action="{{ $editando ? route('inmuebles.contratos.update', [$inmobiliario->id_inmueble, $contrato->id_contrato]) : route('inmuebles.contratos.store', $inmobiliario->id_inmueble) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
    @csrf
    @if ($editando)
        @method('PUT')
    @endif

    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <p class="font-semibold">Revisa los datos del contrato:</p>
            <ul class="mt-2 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="numero_contrato" class="mb-1.5 block text-sm font-medium text-slate-700">Número de contrato</label>
            <input id="numero_contrato" name="numero_contrato" value="{{ old('numero_contrato', $contrato->numero_contrato ?? '') }}" maxlength="50" required class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
        </div>
        <div>
            <label for="tipo_contrato" class="mb-1.5 block text-sm font-medium text-slate-700">Tipo de contrato</label>
            <select id="tipo_contrato" name="tipo_contrato" required class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                @foreach ($tipos as $tipo)
                    <option value="{{ $tipo }}" @selected(old('tipo_contrato', $contrato->tipo_contrato ?? 'Arrendamiento') === $tipo)>{{ $tipo }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="contraparte" class="mb-1.5 block text-sm font-medium text-slate-700">Contraparte (arrendatario, comprador, etc.)</label>
            <input id="contraparte" name="contraparte" value="{{ old('contraparte', $contrato->contraparte ?? '') }}" maxlength="150" required class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
        </div>
        <div>
            <label for="documento_contraparte" class="mb-1.5 block text-sm font-medium text-slate-700">Documento de la contraparte</label>
            <input id="documento_contraparte" name="documento_contraparte" value="{{ old('documento_contraparte', $contrato->documento_contraparte ?? '') }}" maxlength="50" class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
        </div>
        <div>
            <label for="fecha_inicio" class="mb-1.5 block text-sm font-medium text-slate-700">Fecha de inicio</label>
            <input id="fecha_inicio" name="fecha_inicio" type="date" value="{{ old('fecha_inicio', isset($contrato) ? $contrato->fecha_inicio->format('Y-m-d') : '') }}" required class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
        </div>
        <div>
            <label for="fecha_fin" class="mb-1.5 block text-sm font-medium text-slate-700">Fecha de finalización</label>
            <input id="fecha_fin" name="fecha_fin" type="date" value="{{ old('fecha_fin', isset($contrato) && $contrato->fecha_fin ? $contrato->fecha_fin->format('Y-m-d') : '') }}" class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
        </div>
        <div>
            <label for="valor" class="mb-1.5 block text-sm font-medium text-slate-700">Valor</label>
            <input id="valor" name="valor" type="number" min="0" step="0.01" value="{{ old('valor', $contrato->valor ?? '') }}" class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
        </div>
        <div>
            <label for="estado" class="mb-1.5 block text-sm font-medium text-slate-700">Estado</label>
            <select id="estado" name="estado" required class="w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                @foreach ($estados as $estado)
                    <option value="{{ $estado }}" @selected(old('estado', $contrato->estado ?? 'Vigente') === $estado)>{{ $estado }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:col-span-2">
            <label for="observaciones" class="mb-1.5 block text-sm font-medium text-slate-700">Observaciones</label>
            <textarea id="observaciones" name="observaciones" rows="4" class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">{{ old('observaciones', $contrato->observaciones ?? '') }}</textarea>
        </div>
        <div class="sm:col-span-2">
            <label for="archivos" class="mb-1.5 block text-sm font-medium text-slate-700">Documentos del contrato</label>
            <input id="archivos" name="archivos[]" type="file" accept=".pdf,.doc,.docx,.xls,.xlsx" multiple class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm">
            <p class="mt-1 text-xs text-slate-500">Puedes adjuntar PDF, Word o Excel; máximo 10 archivos de 10 MB cada uno. Al editar, los adjuntos existentes se conservan.</p>
            @if ($editando && $contrato->archivos)
                <ul class="mt-3 space-y-1 text-sm">
                    @foreach ($contrato->archivos as $indice => $archivo)
                        <li><a class="text-primary-600 hover:text-primary-800" href="{{ route('inmuebles.contratos.archivos.show', [$inmobiliario->id_inmueble, $contrato->id_contrato, $indice]) }}" target="_blank" rel="noopener">{{ $archivo['name'] }}</a></li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
        <x-button variant="secondary" href="{{ route('inmuebles.contratos.index', $inmobiliario->id_inmueble) }}">Cancelar</x-button>
        <x-button type="submit">{{ $editando ? 'Guardar cambios' : 'Registrar contrato' }}</x-button>
    </div>
</form>
