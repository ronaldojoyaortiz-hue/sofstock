@extends('layouts.app')

@section('title', 'Inmuebles')
@section('page-title', 'Inmuebles')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Inmuebles</h1>
            <p class="mt-1 text-sm text-slate-500">Administra tus propiedades y su estado.</p>
        </div>
        <x-button href="{{ route('inmuebles.create') }}">Registrar inmueble</x-button>
    </div>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold">Código</th>
                        <th class="px-5 py-3.5 font-semibold">Tipo de inmueble</th>
                        <th class="px-5 py-3.5 font-semibold">Dirección</th>
                        <th class="px-5 py-3.5 font-semibold">Ciudad</th>
                        <th class="px-5 py-3.5 font-semibold">Estado</th>
                        <th class="px-5 py-3.5 text-right font-semibold">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white text-slate-600">
                    @forelse ($inmobiliarios as $inmobiliario)
                        <tr class="hover:bg-slate-50/70">
                            <td class="whitespace-nowrap px-5 py-4 font-medium text-slate-800">{{ $inmobiliario->codigo }}</td>
                            <td class="px-5 py-4">{{ $inmobiliario->tipo_inmueble }}</td>
                            <td class="px-5 py-4">{{ $inmobiliario->direccion }}</td>
                            <td class="px-5 py-4">{{ $inmobiliario->ciudad }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">{{ $inmobiliario->estado }}</span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <div class="flex justify-end items-center gap-2">
                                    <a href="{{ route('inmuebles.show', $inmobiliario->id_inmueble) }}"
                                       class="rounded-md bg-slate-100 p-2 text-slate-700 transition hover:bg-slate-200 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-400"
                                       title="Ver inmueble" aria-label="Ver inmueble">
                                        <span class="block h-5 w-5" aria-hidden="true">@include('layouts.partials.icons', ['icon' => 'eye'])</span>
                                    </a>
                                    <a href="{{ route('inmuebles.edit', $inmobiliario->id_inmueble) }}"
                                       class="rounded-md p-2 text-blue-600 transition hover:bg-blue-50 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-400"
                                       title="Editar inmueble" aria-label="Editar inmueble">
                                        <span class="block h-5 w-5" aria-hidden="true">@include('layouts.partials.icons', ['icon' => 'pencil'])</span>
                                    </a>
                                    <form action="{{ route('inmuebles.destroy', $inmobiliario->id_inmueble) }}" method="POST" onsubmit="return confirm('¿Deseas eliminar este inmueble?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded-md p-2 text-red-600 transition hover:bg-red-50 hover:text-red-800 focus:outline-none focus:ring-2 focus:ring-red-400"
                                                title="Eliminar inmueble" aria-label="Eliminar inmueble">
                                            <span class="block h-5 w-5" aria-hidden="true">@include('layouts.partials.icons', ['icon' => 'trash'])</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-500">Todavía no hay inmuebles registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($inmobiliarios->hasPages())
            <div class="border-t border-slate-100 px-5 py-4">{{ $inmobiliarios->links() }}</div>
        @endif
    </x-card>
@endsection
