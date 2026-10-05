@extends('layouts.app')

@section('title', 'Detalle de factura')
@section('page-title', 'Detalle de factura')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Detalle de factura #{{ $detallefactura->factura?->id_factura ?? $detallefactura->id_factura }}</h1>
            <p class="mt-1 text-sm text-slate-500">Información de la factura y de la línea seleccionada.</p>
        </div>
        <x-button href="{{ route('detallefacturas.index') }}">Volver</x-button>
    </div>

    <x-card>
        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500">Cliente</p>
                <p class="mt-1 text-base font-semibold text-slate-800">{{ $detallefactura->factura?->cliente_nombre ?? 'Cliente no registrado' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500">Fecha</p>
                <p class="mt-1 text-base font-semibold text-slate-800">{{ optional($detallefactura->factura?->fecha)->format('d/m/Y') ?? 'Sin fecha' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500">Producto</p>
                <p class="mt-1 text-base font-semibold text-slate-800">{{ $detallefactura->producto_nombre ?? ($detallefactura->producto?->nombre ?? 'Sin nombre') }}</p>
            </div>
        </div>
    </x-card>

    <x-card class="mt-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Producto</th>
                        <th class="px-4 py-3">Cantidad</th>
                        <th class="px-4 py-3">Precio unitario</th>
                        <th class="px-4 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white text-slate-600">
                    <tr>
                        <td class="px-4 py-3">{{ $detallefactura->producto_nombre ?? ($detallefactura->producto?->nombre ?? 'Sin nombre') }}</td>
                        <td class="px-4 py-3">{{ $detallefactura->cantidad }}</td>
                        <td class="px-4 py-3">${{ number_format($detallefactura->precio_unitario, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-medium">${{ number_format((float) $detallefactura->cantidad * (float) $detallefactura->precio_unitario, 2, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </x-card>
@endsection
