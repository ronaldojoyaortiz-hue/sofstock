@extends('layouts.app')

@section('title', 'Detalles de factura')
@section('page-title', 'Detalles de factura')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Detalles de factura</h1>
            <p class="mt-1 text-sm text-slate-500">Consulta los registros de factura y crea nuevas facturas.</p>
        </div>
        <x-button href="{{ route('detallefacturas.create') }}">Nueva factura</x-button>
    </div>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Número</th>
                        <th class="px-5 py-3">Cliente</th>
                        <th class="px-5 py-3">Fecha</th>
                        <th class="px-5 py-3">Estado</th>
                        <th class="px-5 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white text-slate-600">
                    @forelse ($facturas as $factura)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-5 py-4 font-medium text-slate-800">{{ $factura->numero }}</td>
                            <td class="px-5 py-4">{{ $factura->cliente_nombre }}</td>
                            <td class="px-5 py-4">{{ $factura->fecha->format('d/m/Y') }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium capitalize">{{ $factura->estado }}</span>
                            </td>
                            <td class="px-5 py-4 text-right font-medium">${{ number_format($factura->total, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-slate-500">No hay facturas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
@endsection
