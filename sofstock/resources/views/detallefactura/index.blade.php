@extends('layouts.app')

@section('title', 'Detalle de factura')
@section('page-title', 'Detalle de factura')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Detalle de factura</h1>
            <p class="mt-1 text-sm text-slate-500">Visualiza los productos incluidos en la factura.</p>
        </div>
        <x-button href="{{ route('facturas.index') }}">Volver a facturas</x-button>
    </div>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Producto</th>
                        <th class="px-5 py-3">Cantidad</th>
                        <th class="px-5 py-3">Precio unitario</th>
                        <th class="px-5 py-3">Subtotal</th>
                        <th class="px-5 py-3 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white text-slate-600">
                    @forelse ($detalleFacturas as $detalle)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-5 py-4 font-medium text-slate-800">
                                {{ $detalle->producto?->nombre ?? 'Producto no encontrado' }}
                            </td>
                            <td class="px-5 py-4">{{ $detalle->cantidad }}</td>
                            <td class="px-5 py-4">${{ number_format($detalle->precio_unitario, 2, ',', '.') }}</td>
                            <td class="px-5 py-4 text-right font-medium">
                                ${{ number_format($detalle->subtotal, 2, ',', '.') }}
                            </td>
                            <td class="px-5 py-4">
                                <a href="{{ route('detallefacturas.show', $detalle) }}" class="text-blue-600 hover:text-blue-800">Ver</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-slate-500">No hay detalles registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
@endsection