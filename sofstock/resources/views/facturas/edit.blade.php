@extends('layouts.app')

@section('title', 'Editar factura')
@section('page-title', 'Editar factura')

@section('content')
    @php
        $productosMap = $productos->map(fn ($producto) => [
            'id' => (int) $producto->id,
            'nombre' => $producto->nombre,
            'precio_base' => (float) $producto->precio_base,
        ])->values()->all();

        $detallesIniciales = $factura->detalles->map(function ($detalle) {
            return [
                'id_producto' => (string) ($detalle->id_producto ?? ''),
                'cantidad' => (float) ($detalle->cantidad ?? 1),
                'precio_unitario' => (float) ($detalle->precio_unitario ?? 0),
                'subtotal' => (float) ($detalle->subtotal ?? 0),
            ];
        })->values()->all();
    @endphp

    <h1 class="mb-6 text-2xl font-bold text-slate-800">Editar factura #{{ $factura->id_factura }}</h1>
    <x-card>
        <form action="{{ route('facturas.update', $factura) }}" method="POST" class="space-y-6">
            @csrf @method('PUT')
            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid gap-4 md:grid-cols-2">
                <x-input name="fecha" type="date" label="Fecha" :value="old('fecha', $factura->fecha->format('Y-m-d'))" required />
                <x-input name="cliente_nombre" label="Nombre del cliente" :value="old('cliente_nombre', $factura->cliente_nombre)" required />
                <x-input name="cliente_documento" label="Documento del cliente" :value="old('cliente_documento', $factura->cliente_documento)" />
                <x-input name="descuento" type="number" step="0.01" min="0" label="Descuento total" :value="old('descuento', $factura->descuento)" />
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Estado</label>
                    <select name="estado" class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm">
                        @foreach (['borrador', 'emitida', 'pagada', 'anulada'] as $estado)
                            <option value="{{ $estado }}" @selected(old('estado', $factura->estado) === $estado)>{{ ucfirst($estado) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="space-y-4">
                <h2 class="mb-3 font-semibold text-slate-800">Productos de la factura</h2>
                <div id="detalle-factura-container" class="space-y-3"></div>
                <button id="add-product-row" type="button" class="rounded-lg border border-dashed border-slate-300 px-3 py-2 text-sm font-medium text-slate-600 hover:border-slate-400 hover:text-slate-800">+ Agregar producto</button>
            </div>

            <style>
                input[type="number"].cantidad-input::-webkit-outer-spin-button,
                input[type="number"].cantidad-input::-webkit-inner-spin-button {
                    -webkit-appearance: none;
                    margin: 0;
                }

                input[type="number"].cantidad-input {
                    -moz-appearance: textfield;
                    appearance: textfield;
                }
            </style>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Observaciones</label>
                <textarea name="observaciones" rows="3" class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm">{{ old('observaciones', $factura->observaciones) }}</textarea>
            </div>

            <div class="ml-auto max-w-xs space-y-2 text-sm text-slate-700">
                <div class="flex items-center justify-between"><span>Subtotal:</span><strong id="resumen-subtotal" class="text-slate-900">$0</strong></div>
                <div class="flex items-center justify-between"><span>Descuento:</span><strong id="resumen-descuento" class="text-slate-900">$0</strong></div>
                <div class="flex items-center justify-between border-t border-slate-200 pt-2 text-base font-semibold text-slate-900"><span>TOTAL:</span><strong id="resumen-total">$0</strong></div>
            </div>

            <div class="flex justify-end gap-3">
                <x-button variant="secondary" href="{{ route('facturas.index') }}">Cancelar</x-button>
                <x-button type="submit">Actualizar factura</x-button>
            </div>
        </form>
    </x-card>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const products = @json($productosMap);
            const container = document.getElementById('detalle-factura-container');
            const addButton = document.getElementById('add-product-row');
            const existingRows = @json($detallesIniciales);
            let rowIndex = 0;

            const formatCurrency = (value) => {
                const numeric = Number(value) || 0;
                return new Intl.NumberFormat('es-CO', {
                    style: 'currency',
                    currency: 'COP',
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                }).format(numeric);
            };

            const buildRow = (preset = {}) => {
                const row = document.createElement('div');
                row.className = 'grid gap-3 md:grid-cols-4';
                row.dataset.facturaRow = 'true';

                const selectedProductId = preset.id_producto ?? '';
                const quantityValue = preset.cantidad ?? '';
                const priceValue = preset.precio_unitario ?? 0;
                const subtotalValue = preset.subtotal ?? 0;

                row.innerHTML = `
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Producto</label>
                        <select name="detalles[${rowIndex}][id_producto]" data-product-select class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                            <option value="">Seleccione un producto</option>
                            ${products.map((product) => `<option value="${product.id}" ${String(product.id) === String(selectedProductId) ? 'selected' : ''}>${product.nombre}</option>`).join('')}
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Cantidad</label>
                        <div class="flex items-center gap-2">
                            <input name="detalles[${rowIndex}][cantidad]" data-cantidad type="number" min="0" step="1" inputmode="numeric" value="${quantityValue === '' ? '' : Number(quantityValue) || 0}" class="cantidad-input w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                            <button type="button" data-quantity-plus class="rounded-lg border border-slate-300 bg-slate-100 px-2.5 py-2 text-base font-semibold text-slate-700 hover:bg-slate-200" aria-label="Aumentar cantidad">+</button>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Precio unitario</label>
                        <input name="detalles[${rowIndex}][precio_unitario]" data-price type="number" min="0" step="0.01" value="${priceValue}" readonly class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Total</label>
                        <input name="detalles[${rowIndex}][subtotal]" data-subtotal type="text" value="${subtotalValue}" readonly class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm" />
                    </div>
                `;

                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.textContent = 'Eliminar';
                removeButton.className = 'ml-auto mt-2 text-xs font-medium text-red-600 hover:text-red-700';
                removeButton.addEventListener('click', () => {
                    row.remove();
                    updateSummary();
                });

                row.appendChild(removeButton);
                const select = row.querySelector('[data-product-select]');
                const qtyInput = row.querySelector('[data-cantidad]');
                const plusButton = row.querySelector('[data-quantity-plus]');

                select.addEventListener('change', updateSummary);

                qtyInput.addEventListener('input', () => {
                    if (!qtyInput.value) {
                        updateSummary();
                        return;
                    }

                    const value = Number(qtyInput.value);
                    if (value < 0) {
                        qtyInput.value = 0;
                    }
                    updateSummary();
                });

                plusButton.addEventListener('click', () => {
                    const current = Number(qtyInput.value) || 0;
                    qtyInput.value = current + 1;
                    updateSummary();
                });

                container.appendChild(row);
                rowIndex += 1;
            };

            const updateSummary = () => {
                let subtotal = 0;
                const rows = container.querySelectorAll('[data-factura-row]');

                rows.forEach((row) => {
                    const select = row.querySelector('[data-product-select]');
                    const quantityInput = row.querySelector('[data-cantidad]');
                    const priceInput = row.querySelector('[data-price]');
                    const subtotalInput = row.querySelector('[data-subtotal]');
                    const selectedProduct = products.find((product) => String(product.id) === String(select?.value ?? ''));
                    const quantity = Number(quantityInput?.value || 0);
                    const price = selectedProduct ? Number(selectedProduct.precio_base) : 0;
                    const lineSubtotal = quantity * price;

                    if (priceInput) priceInput.value = Number(price).toFixed(2);
                    if (subtotalInput) subtotalInput.value = Number(lineSubtotal).toFixed(2);

                    subtotal += lineSubtotal;
                });

                const descuentoInput = document.querySelector('[name="descuento"]');
                const descuento = Number(descuentoInput?.value || 0);
                document.getElementById('resumen-subtotal').textContent = formatCurrency(subtotal);
                document.getElementById('resumen-descuento').textContent = formatCurrency(descuento);
                document.getElementById('resumen-total').textContent = formatCurrency(Math.max(subtotal - descuento, 0));
            };

            addButton.addEventListener('click', () => buildRow());
            document.querySelector('[name="descuento"]').addEventListener('input', updateSummary);

            if (Array.isArray(existingRows) && existingRows.length > 0) {
                existingRows.forEach((rowData) => buildRow(rowData));
            } else {
                buildRow();
            }

            updateSummary();
        });
    </script>
@endsection
