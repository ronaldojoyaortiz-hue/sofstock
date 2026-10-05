@extends('layouts.app')

@section('title', 'Facturas')
@section('page-title', 'Facturas')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Facturas</h1>
            <p class="mt-1 text-sm text-slate-500">Administra las facturas registradas.</p>
        </div>
        <x-button href="{{ route('facturas.create') }}">Nueva factura</x-button>
    </div>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="px-5 py-3">Número</th><th class="px-5 py-3">Cliente</th><th class="px-5 py-3">Fecha</th><th class="px-5 py-3">Estado</th><th class="px-5 py-3 text-right">Total</th><th class="px-5 py-3 text-right">Acciones</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white text-slate-600">
                    @forelse ($facturas as $factura)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-5 py-4 font-medium text-slate-800">{{ $factura->numero }}</td>
                            <td class="px-5 py-4">{{ $factura->cliente_nombre }}</td>
                            <td class="px-5 py-4">{{ $factura->fecha->format('d/m/Y') }}</td>
                            <td class="px-5 py-4"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium capitalize">{{ $factura->estado }}</span></td>
                            <td class="px-5 py-4 text-right font-medium">${{ number_format($factura->total, 2, ',', '.') }}</td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('facturas.show', $factura->id_factura) }}" class="p-1 text-slate-600 hover:text-slate-900" title="Ver factura" aria-label="Ver factura">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('facturas.edit', $factura->id_factura) }}" class="p-1 text-blue-600 hover:text-blue-900" title="Editar factura" aria-label="Editar factura">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <form action="{{ route('facturas.destroy', $factura->id_factura) }}" method="POST" onsubmit="return confirm('¿Eliminar esta factura?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-red-600 hover:text-red-900" title="Eliminar factura" aria-label="Eliminar factura">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-slate-500">No hay facturas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
@endsection
