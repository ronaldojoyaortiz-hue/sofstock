@extends('layouts.app')

@section('title', 'Reportes')
@section('page-title', 'Reportes')

@section('content')
@php
    $usuario = auth()->user();
    $esAdministrador = $usuario->hasRole('administrador')
        || mb_strtolower(trim($usuario->rol?->nombre_rol ?? '')) === 'administrador';
@endphp

<div x-data="{ generarAbierto: @js(old('periodo') !== null), periodo: @js(old('periodo', 'mensual')) }">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Reportes</h1>
            <p class="mt-1 text-sm text-slate-500">Genera reportes por período y consulta los que ya guardaste.</p>
        </div>
        <x-button type="button" @click="generarAbierto = true">Generar reporte</x-button>
    </div>

    @unless ($tablasFinancierasListas)
        <div class="mb-6 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Para guardar reportes y registrar productos dañados, primero hay que aplicar las migraciones pendientes. En la consola se requiere PHP 8.4.1 o superior y ejecutar <code>php artisan migrate</code>.
        </div>
    @endunless

    @if ($tablasFinancierasListas && ($esAdministrador || $usuario->can('crear reportes')))
        <x-card title="Registrar producto dañado o vencido" subtitle="Al guardar, se descuenta la cantidad del inventario y queda registrada la fecha y la pérdida." class="mb-6">
            @if ($productos->isEmpty())
                <p class="text-sm text-slate-500">No hay productos activos para registrar.</p>
            @else
                <form method="POST" action="{{ route('reports.spoilage.store') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
                    @csrf
                    <div>
                        <label for="id_producto" class="mb-1 block text-sm font-medium text-slate-700">Producto</label>
                        <select id="id_producto" name="id_producto" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
                            @foreach ($productos as $producto)
                                <option value="{{ $producto->id }}" @selected(old('id_producto') == $producto->id)>{{ $producto->nombre }} — stock: {{ $producto->stock_actual }}</option>
                            @endforeach
                        </select>
                        @error('id_producto') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="cantidad" class="mb-1 block text-sm font-medium text-slate-700">Cantidad dañada</label>
                        <input id="cantidad" name="cantidad" type="number" min="1" step="1" required value="{{ old('cantidad') }}" class="no-spinner w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
                        @error('cantidad') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="valor_perdida" class="mb-1 block text-sm font-medium text-slate-700">Valor de la pérdida</label>
                        <input id="valor_perdida" name="valor_perdida" type="number" min="0.01" step="0.01" required value="{{ old('valor_perdida') }}" class="no-spinner w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
                        @error('valor_perdida') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="fecha_merma" class="mb-1 block text-sm font-medium text-slate-700">Fecha</label>
                        <input id="fecha_merma" name="fecha" type="date" max="{{ now()->toDateString() }}" required value="{{ old('fecha', $fechaPredeterminada) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
                        @error('fecha') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="motivo" class="mb-1 block text-sm font-medium text-slate-700">Motivo</label>
                        <input id="motivo" name="motivo" type="text" maxlength="180" required value="{{ old('motivo') }}" placeholder="Vencido, dañado, etc." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
                        @error('motivo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2 xl:col-span-5"><x-button type="submit">Guardar producto dañado</x-button></div>
                </form>
            @endif
        </x-card>
    @endif

    <x-card title="Reportes guardados" subtitle="Abre un reporte para consultar el detalle completo." :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">ID</th>
                        <th class="px-5 py-3">Fecha de generación</th>
                        <th class="px-5 py-3">Tipo de reporte</th>
                        <th class="px-5 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white text-slate-600">
                    @forelse ($reportes as $reporte)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-5 py-4 font-medium text-slate-800">{{ $reporte->id }}</td>
                            <td class="px-5 py-4">{{ $reporte->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-4 capitalize">{{ $reporte->tipo_reporte }}</td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('reports.show', $reporte) }}" class="rounded-lg p-1.5 text-slate-600 hover:bg-slate-100 hover:text-slate-900" title="Ver reporte" aria-label="Ver reporte {{ $reporte->id }}">
                                        <span class="block h-5 w-5">@include('layouts.partials.icons', ['icon' => 'eye'])</span>
                                    </a>
                                    @if ($esAdministrador || $usuario->can('editar reportes'))
                                        <a href="{{ route('reports.edit', $reporte) }}" class="rounded-lg p-1.5 text-blue-600 hover:bg-blue-50 hover:text-blue-800" title="Editar reporte" aria-label="Editar reporte {{ $reporte->id }}">
                                            <span class="block h-5 w-5">@include('layouts.partials.icons', ['icon' => 'pencil'])</span>
                                        </a>
                                    @endif
                                    @if ($esAdministrador || $usuario->can('eliminar reportes'))
                                        <form method="POST" action="{{ route('reports.destroy', $reporte) }}" onsubmit="return confirm('¿Eliminar este reporte guardado?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="rounded-lg p-1.5 text-red-600 hover:bg-red-50 hover:text-red-800" title="Eliminar reporte" aria-label="Eliminar reporte {{ $reporte->id }}">
                                                <span class="block h-5 w-5">@include('layouts.partials.icons', ['icon' => 'trash'])</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-12 text-center text-slate-500">Todavía no has generado reportes.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($reportes instanceof \Illuminate\Contracts\Pagination\Paginator || $reportes instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
            <div class="border-t border-slate-100 px-5 py-4">{{ $reportes->links() }}</div>
        @endif
    </x-card>

    <div x-show="generarAbierto" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" @keydown.escape.window="generarAbierto = false">
        <div class="w-full max-w-xl rounded-xl bg-white p-6 shadow-xl" @click.outside="generarAbierto = false">
            <div class="mb-5 flex items-start justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">Generar reporte</h2>
                    <p class="mt-1 text-sm text-slate-500">El reporte se guardará y aparecerá en la lista.</p>
                </div>
                <button type="button" @click="generarAbierto = false" class="text-slate-400 hover:text-slate-700" aria-label="Cerrar">&times;</button>
            </div>
            @if ($tablasFinancierasListas)
                <form method="POST" action="{{ route('reports.generate') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="periodo" class="mb-1 block text-sm font-medium text-slate-700">Tipo de reporte</label>
                        <select id="periodo" name="periodo" x-model="periodo" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
                            <option value="diario">Diario</option>
                            <option value="semanal">Semanal</option>
                            <option value="mensual">Mensual</option>
                            <option value="anual">Anual</option>
                            <option value="personalizado">Rango personalizado</option>
                        </select>
                    </div>
                    <div x-show="['diario', 'semanal'].includes(periodo)">
                        <label for="referencia" class="mb-1 block text-sm font-medium text-slate-700">Fecha de referencia</label>
                        <input id="referencia" name="referencia" type="date" max="{{ $fechaPredeterminada }}" :required="['diario', 'semanal'].includes(periodo)" value="{{ old('referencia', $fechaPredeterminada) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
                    </div>
                    <div x-show="periodo === 'mensual'" x-cloak>
                        <label for="mes" class="mb-1 block text-sm font-medium text-slate-700">Mes a consultar</label>
                        <input id="mes" name="mes" type="month" min="2000-01" max="{{ now()->format('Y-m') }}" :required="periodo === 'mensual'" value="{{ old('mes', now()->format('Y-m')) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
                    </div>
                    <div x-show="periodo === 'anual'" x-cloak>
                        <label for="anio" class="mb-1 block text-sm font-medium text-slate-700">Año a consultar</label>
                        <input id="anio" name="anio" type="number" min="2000" max="{{ now()->year }}" :required="periodo === 'anual'" value="{{ old('anio', now()->year) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
                    </div>
                    <div x-show="periodo === 'personalizado'" x-cloak class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="fecha_desde" class="mb-1 block text-sm font-medium text-slate-700">Desde</label>
                            <input id="fecha_desde" name="fecha_desde" type="date" :required="periodo === 'personalizado'" value="{{ old('fecha_desde', $fechaPredeterminada) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
                        </div>
                        <div>
                            <label for="fecha_hasta" class="mb-1 block text-sm font-medium text-slate-700">Hasta</label>
                            <input id="fecha_hasta" name="fecha_hasta" type="date" :required="periodo === 'personalizado'" value="{{ old('fecha_hasta', $fechaPredeterminada) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <x-button type="button" variant="secondary" @click="generarAbierto = false">Cancelar</x-button>
                        <x-button type="submit">Generar y guardar</x-button>
                    </div>
                </form>
            @else
                <p class="rounded-lg bg-amber-50 p-4 text-sm text-amber-900">Aplica las migraciones pendientes para poder guardar reportes.</p>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    input.no-spinner::-webkit-outer-spin-button,
    input.no-spinner::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    input.no-spinner {
        appearance: textfield;
        -moz-appearance: textfield;
    }
</style>
@endpush
