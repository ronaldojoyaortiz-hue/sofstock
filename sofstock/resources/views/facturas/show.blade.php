@extends('layouts.app')

@section('title', 'Factura')
@section('page-title', 'Factura')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Factura</h1>
            <p class="mt-1 text-sm text-slate-500">#{{ $factura->numero ?? $factura->id_factura }}</p>
        </div>
        <x-button href="{{ route('facturas.index') }}">Volver</x-button>
    </div>

    <x-card>
        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500">Cliente</p>
                <p class="mt-1 text-lg font-semibold text-slate-800">{{ $factura->cliente_nombre }}</p>
            </div>
            <div class="text-left md:text-right">
                <p class="text-xs uppercase tracking-wide text-slate-500">Fecha</p>
                <p class="mt-1 text-lg font-semibold text-slate-800">{{ $factura->fecha->format('d/m/Y') }}</p>
            </div>
        </div>
    </x-card>

    <x-card class="mt-6">
        <div class="overflow-x-auto">
            <table class="min-w-full border-separate border-spacing-0 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Producto</th>
                        <th class="px-4 py-3">Cantidad</th>
                        <th class="px-4 py-3">Precio unitario</th>
                        <th class="px-4 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white text-slate-600">
                    @forelse ($factura->detalles as $detalle)
                        <tr class="align-top">
                            <td class="border-t border-slate-200 px-4 py-3">{{ $detalle->producto_nombre ?? ($detalle->producto?->nombre ?? 'Producto') }}</td>
                            <td class="border-t border-slate-200 px-4 py-3">{{ number_format($detalle->cantidad, 0, ',', '.') }}</td>
                            <td class="border-t border-slate-200 px-4 py-3">${{ number_format($detalle->precio_unitario, 2, ',', '.') }}</td>
                            <td class="border-t border-slate-200 px-4 py-3 text-right font-medium">${{ number_format(((float) $detalle->cantidad * (float) $detalle->precio_unitario), 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="border-t border-slate-200 px-4 py-6 text-center text-slate-500">No hay productos en esta factura.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex justify-end">
            <div class="w-full max-w-sm space-y-3 rounded-lg border border-slate-200 bg-slate-50 p-4">
                <div class="flex items-center justify-between text-sm text-slate-600">
                    <span>Subtotal</span>
                    <span>${{ number_format($factura->subtotal ?? 0, 2, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between text-sm text-slate-600">
                    <span>Descuento</span>
                    <span>${{ number_format($factura->descuento ?? 0, 2, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between text-sm text-slate-600">
                    <span>Impuesto</span>
                    <span>${{ number_format($factura->impuesto ?? 0, 2, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between border-t border-slate-200 pt-3 text-base font-semibold text-slate-900">
                    <span>Total</span>
                    <span>${{ number_format($factura->total ?? 0, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </x-card>
@endsection
