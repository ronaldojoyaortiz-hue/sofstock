@extends('layouts.app')

@section('title', 'Contratos')
@section('page-title', 'Contratos')

@section('content')
    <div class="mb-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Contratos</h1>
                <p class="mt-1 text-sm text-slate-500">Consulta los contratos registrados y sus inmuebles.</p>
            </div>
            <x-button href="{{ route('contratos.create') }}">Registrar contrato</x-button>
        </div>
    </div>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold">Número</th>
                        <th class="px-5 py-3.5 font-semibold">ID</th>
                        <th class="px-5 py-3.5 font-semibold">Inmueble</th>
                        <th class="px-5 py-3.5 font-semibold">Tipo</th>
                        <th class="px-5 py-3.5 font-semibold">Contraparte</th>
                        <th class="px-5 py-3.5 font-semibold">Vigencia</th>
                        <th class="px-5 py-3.5 font-semibold">Estado</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-600">
                    @forelse ($contratos as $contrato)
                        <tr>
                            <td class="px-5 py-4 font-medium text-slate-800">
                                {{ $contrato->numero_contrato }}
                            </td>
                            <td class="px-5 py-4 text-slate-500">{{ $contrato->id_contrato }}</td>
                            <td class="px-5 py-4">
                                <span class="block font-medium text-slate-800">{{ $contrato->inmueble_codigo }}</span>
                                <span class="text-xs text-slate-500">{{ $contrato->inmueble_direccion }}, {{ $contrato->inmueble_ciudad }}</span>
                            </td>
                            <td class="px-5 py-4">{{ $contrato->tipo_contrato }}</td>
                            <td class="px-5 py-4">{{ $contrato->contraparte }}</td>
                            <td class="px-5 py-4">
                                {{ $contrato->fecha_inicio->format('d/m/Y') }}
                                – {{ $contrato->fecha_fin?->format('d/m/Y') ?? 'Abierto' }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                                    {{ $contrato->estado }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a
                                    class="inline-flex rounded-md bg-slate-100 p-2 text-slate-700 hover:bg-slate-200 hover:text-slate-900"
                                    href="{{ route('inmuebles.contratos.show', [$contrato->id_inmueble, $contrato->id_contrato]) }}"
                                    title="Ver contrato"
                                    aria-label="Ver contrato"
                                >
                                    <span class="block h-5 w-5" aria-hidden="true">@include('layouts.partials.icons', ['icon' => 'eye'])</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-slate-500">
                                Todavía no hay contratos registrados.
                                <a href="{{ route('inmuebles.index') }}" class="font-medium text-primary-600 hover:text-primary-800">
                                    Ver inmuebles
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($contratos->hasPages())
            <div class="border-t border-slate-100 px-5 py-4">{{ $contratos->links() }}</div>
        @endif
    </x-card>
@endsection
