@extends('layouts.app')

@section('title', 'Contratos del inmueble')
@section('page-title', 'Contratos')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-slate-500">Inmueble: {{ $inmobiliario->codigo }}</p>
            <h1 class="text-2xl font-bold text-slate-800">Contratos del inmueble {{ $inmobiliario->codigo }}</h1>
        </div>
        <div class="flex gap-3">
            <x-button variant="secondary" href="{{ route('inmuebles.show', $inmobiliario->id_inmueble) }}">Volver al inmueble</x-button>
            <x-button href="{{ route('inmuebles.contratos.create', $inmobiliario->id_inmueble) }}">Nuevo contrato</x-button>
        </div>
    </div>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold">ID</th>
                        <th class="px-5 py-3.5 font-semibold">Número</th>
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
                            <td class="px-5 py-4 text-slate-500">{{ $contrato->id_contrato }}</td>
                            <td class="px-5 py-4 font-medium text-slate-800">{{ $contrato->numero_contrato }}</td>
                            <td class="px-5 py-4">{{ $contrato->tipo_contrato }}</td>
                            <td class="px-5 py-4">{{ $contrato->contraparte }}</td>
                            <td class="px-5 py-4">{{ $contrato->fecha_inicio->format('d/m/Y') }} – {{ $contrato->fecha_fin?->format('d/m/Y') ?? 'Abierto' }}</td>
                            <td class="px-5 py-4"><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">{{ $contrato->estado }}</span></td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-3">
                                    <a class="font-medium text-primary-600 hover:text-primary-800" href="{{ route('inmuebles.contratos.show', [$inmobiliario->id_inmueble, $contrato->id_contrato]) }}">Ver</a>
                                    <a class="font-medium text-blue-600 hover:text-blue-800" href="{{ route('inmuebles.contratos.edit', [$inmobiliario->id_inmueble, $contrato->id_contrato]) }}">Editar</a>
                                    <form action="{{ route('inmuebles.contratos.destroy', [$inmobiliario->id_inmueble, $contrato->id_contrato]) }}" method="POST" onsubmit="return confirm('¿Deseas eliminar este contrato?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="font-medium text-red-600 hover:text-red-800" type="submit">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-12 text-center text-slate-500">Este inmueble todavía no tiene contratos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($contratos->hasPages())
            <div class="border-t border-slate-100 px-5 py-4">{{ $contratos->links() }}</div>
        @endif
    </x-card>
@endsection
