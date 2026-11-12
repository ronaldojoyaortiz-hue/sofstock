@extends('layouts.app')

@section('title', 'Editar acta')
@section('page-title', 'Editar acta')

@section('content')
@php
    $esEdicion = $acta !== null;
    $tipoSeleccionado = old('tipo_acta', $acta?->tipo_acta ?? '');
    $productosActa = old('productos', $acta?->productos ?? []);
    $productosActa = $productosActa ?: [['producto_id' => '', 'cantidad' => '1', 'observaciones' => '']];
    $siguienteIndiceProducto = max(array_map('intval', array_keys($productosActa))) + 1;
    $firmantesIniciales = array_values(old('firmantes', $acta?->firmantesParaMostrar() ?? []));
    $firmantesIniciales = $firmantesIniciales ?: [['rol_id' => '', 'usuario_id' => '']];
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
        font-size: 12pt;
        line-height: 1.5;
        box-shadow: 0 8px 24px rgb(15 23 42 / 10%);
    }

    .acta-document input:not([type="checkbox"]):not([type="radio"]),
    .acta-document select,
    .acta-document textarea {
        font-family: "Times New Roman", Times, serif;
        font-size: 12pt;
        background: transparent;
        color: inherit;
        border: 0;
        border-bottom: 1px solid #9ca3af;
        border-radius: 0;
        box-shadow: none;
    }

    .acta-document input:not([type="checkbox"]):not([type="radio"]),
    .acta-document select {
        padding: 4px 0;
    }

    .acta-document textarea {
        min-height: 84px;
        padding: 8px;
        border: 1px solid #9ca3af;
        resize: vertical;
    }

    .acta-document #productos-section,
    .acta-document #inmueble-section,
    .acta-document fieldset {
        border: 0;
        border-top: 1px solid #9ca3af;
        border-radius: 0;
        background: transparent;
        padding: 16px 0 0;
    }

    .acta-document .product-row {
        border-bottom: 1px solid #d1d5db;
        border-radius: 0;
        background: transparent;
        padding: 12px 0;
    }

    .acta-document input:not([type="checkbox"]):not([type="radio"]):focus,
    .acta-document select:focus,
    .acta-document textarea:focus {
        outline: 2px solid #2563eb;
        outline-offset: 2px;
    }

    html.dark .acta-document {
        background: #000;
        color: #f9fafb;
        border-color: #374151;
    }

    html.dark .acta-document input:not([type="checkbox"]):not([type="radio"]),
    html.dark .acta-document select,
    html.dark .acta-document textarea {
        color: #f9fafb;
        border-color: #6b7280;
        color-scheme: dark;
    }

    html.dark .acta-document .bg-white,
    html.dark .acta-document .bg-gray-50 {
        background-color: #0a0a0a !important;
    }

    html.dark .acta-document .text-gray-800,
    html.dark .acta-document .text-gray-700 {
        color: #f3f4f6 !important;
    }

    html.dark .acta-document .text-gray-600,
    html.dark .acta-document .text-gray-500 {
        color: #d1d5db !important;
    }

    @page {
        size: A4 portrait;
        margin: 0;
    }

    @media print {
        body {
            margin: 0;
        }

        body * { visibility: hidden; }
        .acta-document, .acta-document * { visibility: visible; }
        .acta-document {
            position: absolute;
            inset: 0 auto auto 0;
            width: 210mm;
            min-height: 297mm;
            border: 0;
            box-shadow: none;
            background: #fff !important;
            color: #111827 !important;
        }
        .acta-document input:not([type="checkbox"]):not([type="radio"]),
        .acta-document select,
        .acta-document textarea {
            color: #111827 !important;
            color-scheme: light;
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

        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <div>
                <label for="tipo_acta" class="mb-1 block text-sm font-medium">1. Selecciona el tipo de acta</label>
                <select id="tipo_acta" name="tipo_acta" required class="w-full rounded-lg border-gray-300">
                    <option value="">Selecciona un tipo</option>
                    <option value="entrega" @selected($tipoSeleccionado === 'entrega')>Acta de entrega</option>
                    <option value="junta_directiva" @selected($tipoSeleccionado === 'junta_directiva')>Junta directiva</option>
                    <option value="asamblea" @selected($tipoSeleccionado === 'asamblea')>Asamblea</option>
                    <option value="inventario" @selected($tipoSeleccionado === 'inventario')>Acta de inventario</option>
                    <option value="devolucion" @selected($tipoSeleccionado === 'devolucion')>Acta de devolución</option>
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
                <div class="mt-5 text-left">
                    <label for="codigo" class="mb-1 block font-semibold">Código del acta</label>
                    <input id="codigo" type="text" name="codigo" value="{{ old('codigo', $acta?->codigo) }}"
                           required maxlength="50" placeholder="Escribe el código del acta" @readonly($esEdicion)
                           class="w-full">
                </div>
                <div class="mt-5 text-left">
                    <label for="titulo" class="mb-1 block font-semibold">Título del acta</label>
                    <input id="titulo" type="text" name="titulo" value="{{ old('titulo', $acta?->titulo) }}"
                           required maxlength="255" placeholder="Escribe el título del acta" class="w-full">
                </div>
            </header>

            <div id="productos-section" @if(!in_array($tipoSeleccionado, ['entrega', 'inventario'], true)) hidden @endif class="space-y-3 rounded border border-gray-400 p-4">
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
                                            {{ $linea['nombre'] ?? $producto->nombre }} (existencia: {{ $producto->stock_actual }})
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

            <div id="inmueble-section" @if(!in_array($tipoSeleccionado, ['entrega', 'devolucion'], true)) hidden @endif class="rounded border border-gray-400 p-4">
                <label for="inmueble_id" class="mb-1 block font-semibold">Inmueble relacionado</label>
                <p class="mb-2 text-sm text-gray-600">Selecciona el inmueble relacionado con la entrega o devolución.</p>
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
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="fecha" class="mb-1 block font-semibold">Fecha</label>
                        <input id="fecha" type="date" name="fecha"
                               value="{{ old('fecha', $acta?->fecha?->format('Y-m-d') ?? date('Y-m-d')) }}"
                               required class="w-full rounded border-gray-400">
                    </div>

                    <div id="hora-section">
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

            @include('actas._firmantes')

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
        const tipo = document.getElementById('tipo_acta');
        const documentSection = document.getElementById('acta-document');
        const documentTitle = document.getElementById('document-title');
        const tipoAyuda = document.getElementById('tipo-ayuda');
        const productosSection = document.getElementById('productos-section');
        const inmuebleSection = document.getElementById('inmueble-section');
        const inmueble = document.getElementById('inmueble_id');
        const horaSection = document.getElementById('hora-section');
        const hora = document.getElementById('hora');
        const productosList = document.getElementById('productos-list');
        const template = document.getElementById('producto-row-template');
        const codeInput = document.getElementById('codigo');
        let nextProductIndex = Number(productosList.dataset.nextIndex);

        const actualizarTipo = () => {
            const tieneTipo = Boolean(tipo.value);
            const muestraProductos = ['entrega', 'inventario'].includes(tipo.value);
            const esDevolucion = tipo.value === 'devolucion';
            const tipoLabels = {
                entrega: 'ACTA DE ENTREGA',
                recibo: 'ACTA DE RECIBO',
                junta_directiva: 'ACTA DE JUNTA DIRECTIVA',
                asamblea: 'ACTA DE ASAMBLEA',
                inventario: 'ACTA DE INVENTARIO',
                devolucion: 'ACTA DE DEVOLUCIÓN',
            };

            documentSection.hidden = !tieneTipo;
            documentSection.querySelectorAll('input, select, textarea, button[type="submit"]').forEach((field) => {
                field.disabled = !tieneTipo;
            });
            documentTitle.textContent = tipoLabels[tipo.value] || 'ACTA';
            productosSection.hidden = !muestraProductos;
            inmuebleSection.hidden = !['entrega', 'devolucion'].includes(tipo.value);
            inmueble.required = tieneTipo && esDevolucion;
            inmueble.disabled = !tieneTipo || !['entrega', 'devolucion'].includes(tipo.value);
            hora.disabled = !tieneTipo;

            productosList.querySelectorAll('.product-select').forEach((select) => {
                select.required = tieneTipo && tipo.value === 'inventario';
                select.disabled = !tieneTipo || !muestraProductos;
            });
            productosList.querySelectorAll('.product-row input').forEach((input) => {
                input.disabled = !tieneTipo || !muestraProductos;
            });

            tipoAyuda.textContent = tieneTipo
                ? 'Documento listo para completar. Los campos específicos corresponden al tipo de acta seleccionado.'
                : 'Selecciona el tipo de acta para abrir el documento y sus campos.';
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
        actualizarTipo();
    })();
</script>
@endsection
