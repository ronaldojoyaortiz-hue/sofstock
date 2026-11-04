@extends('layouts.app')

@section('title', 'Editar acta')
@section('page-title', 'Editar acta')

@section('content')
@php
    $esEdicion = $acta !== null;
    $tipoSeleccionado = old('tipo', $acta?->tipo ?? '');
    $productosActa = old('productos', $acta?->productos->map(fn ($producto) => [
        'producto_id' => $producto->id,
        'cantidad' => $producto->pivot->cantidad,
        'observaciones' => $producto->pivot->observaciones,
    ])->all() ?? []);
    $productosActa = $productosActa ?: [['producto_id' => '', 'cantidad' => '1', 'observaciones' => '']];
    $siguienteIndiceProducto = max(array_map('intval', array_keys($productosActa))) + 1;
    $rolFirmantesSeleccionado = old('rol_firmantes_id', $acta?->firmas->first()?->rol_id);
    $estados = [
        'borrador' => 'Borrador',
        'pendiente_aprobacion' => 'Pendiente de aprobación',
        'aprobada' => 'Aprobada',
        'firmada' => 'Firmada',
        'anulada' => 'Anulada',
    ];
@endphp

<style>
    .acta-document {
        box-sizing: border-box;
        width: min(100%, 210mm);
        min-height: 297mm;
        padding: 22mm 20mm;
        border: 1px solid #d1d5db;
        background: #fff;
        color: #1f2937;
        font-family: "Times New Roman", Times, serif;
        box-shadow: 0 8px 24px rgb(15 23 42 / 10%);
    }

    .acta-document input,
    .acta-document select,
    .acta-document textarea {
        font-family: Arial, sans-serif;
    }

    @media print {
        body * { visibility: hidden; }
        .acta-document, .acta-document * { visibility: visible; }
        .acta-document {
            position: absolute;
            inset: 0 auto auto 0;
            width: 210mm;
            min-height: 297mm;
            border: 0;
            box-shadow: none;
        }
        .acta-document .no-print { display: none; }
    }
</style>

<div class="mx-auto max-w-5xl space-y-5">
    <h2 class="text-xl font-semibold text-gray-800">
        {{ $esEdicion ? 'Modificar acta' : 'Crear acta' }}
    </h2>

    @if($errors->any())
        <div class="rounded-lg bg-red-100 p-4 text-red-700">
            <ul class="list-inside list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ $esEdicion ? route('actas.update', $acta) : route('actas.store') }}" method="POST" class="space-y-5">
        @csrf
        @if($esEdicion)
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 gap-5 rounded-xl border border-gray-200 bg-white p-5 sm:grid-cols-2">
            <div>
                <label for="codigo" class="mb-1 block text-sm font-medium">Código del acta</label>
                <input id="codigo" type="text" name="codigo" value="{{ old('codigo', $acta?->codigo) }}"
                       required maxlength="50" placeholder="Ej. ACTA-2026-001" @readonly($esEdicion)
                       class="w-full rounded-lg border-gray-300">
            </div>

            <div>
                <label for="tipo" class="mb-1 block text-sm font-medium">1. Selecciona el tipo de acta</label>
                <select id="tipo" name="tipo" required class="w-full rounded-lg border-gray-300">
                    <option value="">Selecciona un tipo</option>
                    <option value="entrega_bienes" @selected($tipoSeleccionado === 'entrega_bienes')>Entrega de bienes e inventario</option>
                    <option value="junta_directiva" @selected($tipoSeleccionado === 'junta_directiva')>Junta directiva</option>
                    <option value="asamblea" @selected($tipoSeleccionado === 'asamblea')>Reunión o asamblea</option>
                    <option value="entrega_inmueble" @selected($tipoSeleccionado === 'entrega_inmueble')>Inmobiliario (entrega o devolución)</option>
                </select>
            </div>
        </div>

        <p id="tipo-ayuda" class="text-sm text-gray-600" aria-live="polite">
            Selecciona el tipo de acta para abrir el documento y sus campos.
        </p>

        <section id="acta-document" @if(!$tipoSeleccionado) hidden @endif class="acta-document mx-auto space-y-7">
            <header class="border-b-2 border-gray-800 pb-5 text-center">
                <p class="text-sm uppercase tracking-widest">Documento oficial</p>
                <h1 id="document-title" class="mt-3 text-2xl font-bold">ACTA</h1>
                <p class="mt-2 text-sm">Código: <span id="document-code">{{ old('codigo', $acta?->codigo) }}</span></p>
            </header>

            <div id="productos-section" @if($tipoSeleccionado !== 'entrega_bienes') hidden @endif class="space-y-3 rounded border border-gray-400 p-4">
                <div>
                    <h3 class="font-semibold">Productos entregados</h3>
                    <p class="text-sm text-gray-600">Selecciona los productos y cantidades que forman parte de esta acta.</p>
                </div>

                <div id="productos-list" data-next-index="{{ $siguienteIndiceProducto }}" class="space-y-3">
                    @foreach($productosActa as $indice => $linea)
                        <div class="product-row grid grid-cols-1 items-end gap-3 rounded bg-gray-50 p-3 sm:grid-cols-[minmax(0,2fr)_1fr_minmax(0,2fr)_auto]">
                            <div>
                                <label class="mb-1 block text-sm font-medium">Producto</label>
                                <select name="productos[{{ $indice }}][producto_id]" class="product-select w-full rounded border-gray-300">
                                    <option value="">Selecciona un producto</option>
                                    @foreach($productos as $producto)
                                        <option value="{{ $producto->id }}" @selected((string) ($linea['producto_id'] ?? '') === (string) $producto->id)>
                                            {{ $producto->nombre }} (existencia: {{ $producto->stock_actual }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium">Cantidad</label>
                                <input name="productos[{{ $indice }}][cantidad]" type="number" min="0.01" step="0.01"
                                       value="{{ $linea['cantidad'] ?? '1' }}" class="w-full rounded border-gray-300">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium">Observaciones</label>
                                <input name="productos[{{ $indice }}][observaciones]" type="text" maxlength="500"
                                       value="{{ $linea['observaciones'] ?? '' }}" class="w-full rounded border-gray-300">
                            </div>
                            <button type="button" class="remove-product rounded border border-gray-300 px-3 py-2 text-sm">
                                Quitar
                            </button>
                        </div>
                    @endforeach
                </div>

                <button type="button" id="add-product" class="rounded border border-blue-700 px-4 py-2 text-sm text-blue-700 hover:bg-blue-50">
                    Agregar producto
                </button>
            </div>

            <div id="inmueble-section" @if($tipoSeleccionado !== 'entrega_inmueble') hidden @endif class="rounded border border-gray-400 p-4">
                <label for="inmueble_id" class="mb-1 block font-semibold">Inmobiliario relacionado</label>
                <p class="mb-2 text-sm text-gray-600">Selecciona el inmueble objeto de la entrega o devolución.</p>
                <select id="inmueble_id" name="inmueble_id" class="w-full rounded border-gray-400">
                    <option value="">Selecciona un inmueble</option>
                    @forelse($inmuebles as $inmueble)
                        <option value="{{ $inmueble->id_inmueble }}" @selected((string) old('inmueble_id', $acta?->inmueble_id) === (string) $inmueble->id_inmueble)>
                            {{ $inmueble->codigo }} · {{ $inmueble->tipo_inmueble }} · {{ $inmueble->direccion }}, {{ $inmueble->ciudad }}
                        </option>
                    @empty
                        <option value="" disabled>No hay inmuebles registrados</option>
                    @endforelse
                </select>
                @if($inmuebles->isEmpty())
                    <p class="mt-2 text-sm text-amber-700">Primero registra un inmueble para poder vincularlo al acta.</p>
                @endif
            </div>

            <div class="space-y-5">
                <div>
                    <label for="titulo" class="mb-1 block font-semibold">Título del acta</label>
                    <input id="titulo" type="text" name="titulo" value="{{ old('titulo', $acta?->titulo) }}"
                           required maxlength="255" class="w-full rounded border-gray-400">
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="fecha" class="mb-1 block font-semibold">Fecha</label>
                        <input id="fecha" type="date" name="fecha"
                               value="{{ old('fecha', $acta?->fecha?->format('Y-m-d') ?? date('Y-m-d')) }}"
                               required class="w-full rounded border-gray-400">
                    </div>

                    <div id="hora-section" @if(in_array($tipoSeleccionado, ['entrega_bienes', 'entrega_inmueble'], true)) hidden @endif>
                        <label for="hora" class="mb-1 block font-semibold">Hora</label>
                        <input id="hora" type="time" name="hora" value="{{ old('hora', $acta?->hora) }}"
                               class="w-full rounded border-gray-400">
                    </div>
                </div>

                <div>
                    <label for="lugar" class="mb-1 block font-semibold">Lugar</label>
                    <input id="lugar" type="text" name="lugar" value="{{ old('lugar', $acta?->lugar) }}"
                           maxlength="255" class="w-full rounded border-gray-400">
                </div>

                <div>
                    <label for="estado" class="mb-1 block font-semibold">Estado</label>
                    <select id="estado" name="estado" required class="w-full rounded border-gray-400">
                        @foreach($estados as $valor => $etiqueta)
                            <option value="{{ $valor }}" @selected(old('estado', $acta?->estado ?? 'borrador') === $valor)>{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="objetivo" class="mb-1 block font-semibold">Objetivo</label>
                    <textarea id="objetivo" name="objetivo" rows="3" class="w-full rounded border-gray-400">{{ old('objetivo', $acta?->objetivo) }}</textarea>
                </div>

                <div>
                    <label for="desarrollo" class="mb-1 block font-semibold">Desarrollo de la reunión o actividad</label>
                    <textarea id="desarrollo" name="desarrollo" rows="6" class="w-full rounded border-gray-400">{{ old('desarrollo', $acta?->desarrollo) }}</textarea>
                </div>

                <div>
                    <label for="conclusiones" class="mb-1 block font-semibold">Conclusiones</label>
                    <textarea id="conclusiones" name="conclusiones" rows="4" class="w-full rounded border-gray-400">{{ old('conclusiones', $acta?->conclusiones) }}</textarea>
                </div>
            </div>

            <fieldset class="space-y-4 rounded border border-gray-400 p-4">
                <legend class="px-2 font-semibold">Firmas según el rol</legend>
                <div>
                    <label for="rol_firmantes_id" class="mb-1 block font-medium">Rol que firma esta acta</label>
                    <select id="rol_firmantes_id" name="rol_firmantes_id" required class="w-full rounded border-gray-400">
                        <option value="">Selecciona el rol</option>
                        @foreach($rolesFirmantes as $rolFirmante)
                            <option value="{{ $rolFirmante->id_rol }}"
                                    data-usuarios="{{ $rolFirmante->usuarios->map(fn ($firmante) => ['nombre' => trim($firmante->nombre . ' ' . $firmante->apellido)])->values()->toJson() }}"
                                    @disabled($rolFirmante->usuarios->isEmpty())
                                    @selected((string) $rolFirmantesSeleccionado === (string) $rolFirmante->id_rol)>
                                {{ ucfirst($rolFirmante->nombre_rol) }}{{ $rolFirmante->usuarios->isEmpty() ? ' (sin usuarios activos)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-600">Se incluirá una firma por cada usuario activo del rol seleccionado.</p>
                </div>
                <div id="firmas-preview" class="grid grid-cols-1 gap-x-8 gap-y-12 pt-6 sm:grid-cols-2" aria-live="polite"></div>
            </fieldset>

            <div class="no-print flex flex-wrap justify-end gap-3 border-t border-gray-400 pt-5">
                <a href="{{ route('actas.index') }}" class="rounded border border-gray-400 px-5 py-2">
                    Cancelar
                </a>
                <button type="submit" class="rounded bg-blue-700 px-5 py-2 text-white hover:bg-blue-800">
                    {{ $esEdicion ? 'Actualizar acta' : 'Guardar acta' }}
                </button>
            </div>
        </section>
    </form>
</div>

<template id="producto-row-template">
    <div class="product-row grid grid-cols-1 items-end gap-3 rounded bg-gray-50 p-3 sm:grid-cols-[minmax(0,2fr)_1fr_minmax(0,2fr)_auto]">
        <div>
            <label class="mb-1 block text-sm font-medium">Producto</label>
            <select data-field="producto_id" class="product-select w-full rounded border-gray-300">
                <option value="">Selecciona un producto</option>
                @foreach($productos as $producto)
                    <option value="{{ $producto->id }}">{{ $producto->nombre }} (existencia: {{ $producto->stock_actual }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Cantidad</label>
            <input data-field="cantidad" type="number" min="0.01" step="0.01" value="1" class="w-full rounded border-gray-300">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Observaciones</label>
            <input data-field="observaciones" type="text" maxlength="500" class="w-full rounded border-gray-300">
        </div>
        <button type="button" class="remove-product rounded border border-gray-300 px-3 py-2 text-sm">Quitar</button>
    </div>
</template>

<script>
    (() => {
        const tipo = document.getElementById('tipo');
        const documentSection = document.getElementById('acta-document');
        const documentTitle = document.getElementById('document-title');
        const documentCode = document.getElementById('document-code');
        const tipoAyuda = document.getElementById('tipo-ayuda');
        const productosSection = document.getElementById('productos-section');
        const inmuebleSection = document.getElementById('inmueble-section');
        const inmueble = document.getElementById('inmueble_id');
        const horaSection = document.getElementById('hora-section');
        const hora = document.getElementById('hora');
        const productosList = document.getElementById('productos-list');
        const template = document.getElementById('producto-row-template');
        const roleSelect = document.getElementById('rol_firmantes_id');
        const signaturePreview = document.getElementById('firmas-preview');
        const codeInput = document.getElementById('codigo');
        let nextProductIndex = Number(productosList.dataset.nextIndex);

        const renderSignatures = () => {
            signaturePreview.replaceChildren();
            const option = roleSelect.selectedOptions[0];
            if (!option || !option.value) return;

            const signers = JSON.parse(option.dataset.usuarios || '[]');
            if (!signers.length) {
                const message = document.createElement('p');
                message.className = 'text-sm text-amber-700';
                message.textContent = 'Este rol no tiene usuarios activos asignados.';
                signaturePreview.append(message);
                return;
            }

            signers.forEach((signer) => {
                const block = document.createElement('div');
                block.className = 'pt-10 text-center';

                const line = document.createElement('div');
                line.className = 'border-t border-gray-700 pt-2';

                const name = document.createElement('p');
                name.className = 'font-semibold';
                name.textContent = signer.nombre;

                const role = document.createElement('p');
                role.className = 'text-sm capitalize';
                role.textContent = option.textContent.replace(/\s*\(sin usuarios activos\)\s*/i, '').trim();

                line.append(name, role);
                block.append(line);
                signaturePreview.append(block);
            });
        };

        const actualizarTipo = () => {
            const tieneTipo = Boolean(tipo.value);
            const esEntregaBienes = tipo.value === 'entrega_bienes';
            const esEntregaInmueble = tipo.value === 'entrega_inmueble';
            const tipoLabels = {
                entrega_bienes: 'ACTA DE ENTREGA DE BIENES E INVENTARIO',
                junta_directiva: 'ACTA DE JUNTA DIRECTIVA',
                asamblea: 'ACTA DE REUNIÓN O ASAMBLEA',
                entrega_inmueble: 'ACTA DE ENTREGA O DEVOLUCIÓN DE INMUEBLE',
            };

            documentSection.hidden = !tieneTipo;
            documentSection.querySelectorAll('input, select, textarea, button[type="submit"]').forEach((field) => {
                field.disabled = !tieneTipo;
            });
            documentTitle.textContent = tipoLabels[tipo.value] || 'ACTA';
            documentCode.textContent = codeInput.value;
            productosSection.hidden = !esEntregaBienes;
            inmuebleSection.hidden = !esEntregaInmueble;
            inmueble.required = tieneTipo && esEntregaInmueble;
            inmueble.disabled = !tieneTipo || !esEntregaInmueble;
            horaSection.hidden = esEntregaBienes || esEntregaInmueble;
            hora.disabled = !tieneTipo || esEntregaBienes || esEntregaInmueble;

            productosList.querySelectorAll('.product-select').forEach((select) => {
                select.required = tieneTipo && esEntregaBienes;
                select.disabled = !tieneTipo || !esEntregaBienes;
            });
            productosList.querySelectorAll('.product-row input').forEach((input) => {
                input.disabled = !tieneTipo || !esEntregaBienes;
            });
            roleSelect.disabled = !tieneTipo;

            tipoAyuda.textContent = tieneTipo
                ? 'Documento listo para completar. Los campos específicos corresponden al tipo de acta seleccionado.'
                : 'Selecciona el tipo de acta para abrir el documento y sus campos.';
            renderSignatures();
        };

        document.getElementById('add-product').addEventListener('click', () => {
            const row = template.content.firstElementChild.cloneNode(true);
            row.querySelectorAll('[data-field]').forEach((field) => {
                field.name = `productos[${nextProductIndex}][${field.dataset.field}]`;
            });
            nextProductIndex += 1;
            productosList.appendChild(row);
            actualizarTipo();
        });

        productosList.addEventListener('click', (event) => {
            if (event.target.closest('.remove-product') && productosList.querySelectorAll('.product-row').length > 1) {
                event.target.closest('.product-row').remove();
            }
        });

        tipo.addEventListener('change', actualizarTipo);
        codeInput.addEventListener('input', () => {
            documentCode.textContent = codeInput.value;
        });
        roleSelect.addEventListener('change', renderSignatures);
        actualizarTipo();
    })();
</script>
@endsection
