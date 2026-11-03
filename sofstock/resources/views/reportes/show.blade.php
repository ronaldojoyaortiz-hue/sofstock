@extends('layouts.app')

@section('title', 'Detalle del reporte')
@section('page-title', 'Detalle del reporte')

@section('content')
    @php($datos = $reporte->datos)
    @php($resumen = $datos['resumen'] ?? [])
    @php($usuario = auth()->user())
    @php($esAdministrador = $usuario->hasRole('administrador') || mb_strtolower(trim($usuario->rol?->nombre_rol ?? '')) === 'administrador')

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Reporte #{{ $reporte->id }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ ucfirst($datos['etiqueta'] ?? $reporte->tipo_reporte) }} · {{ $reporte->fecha_desde->format('d/m/Y') }} al {{ $reporte->fecha_hasta->format('d/m/Y') }}</p>
            <p class="mt-1 text-sm text-slate-500">Generado el {{ $reporte->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <div class="flex gap-2">
            <x-button variant="secondary" href="{{ route('reports.index') }}">Volver a reportes</x-button>
            <x-button variant="secondary" href="{{ route('reports.download', ['periodo' => 'personalizado', 'fecha_desde' => $reporte->fecha_desde->toDateString(), 'fecha_hasta' => $reporte->fecha_hasta->toDateString()]) }}">Descargar CSV</x-button>
            @if ($esAdministrador || $usuario->can('editar reportes'))<x-button variant="secondary" href="{{ route('reports.edit', $reporte) }}">Editar</x-button>@endif
            @if ($esAdministrador || $usuario->can('eliminar reportes'))
                <form method="POST" action="{{ route('reports.destroy', $reporte) }}" onsubmit="return confirm('¿Eliminar este reporte guardado?')">
                    @csrf @method('DELETE')
                    <x-button variant="danger" type="submit">Eliminar</x-button>
                </form>
            @endif
        </div>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <x-card><p class="text-sm font-medium text-slate-500">Presupuesto inicial</p><p class="mt-2 text-2xl font-bold text-slate-800">{{ $resumen['presupuesto_inicial'] === null ? 'No registrado' : '$' . number_format((float) $resumen['presupuesto_inicial'], 2, ',', '.') }}</p></x-card>
        <x-card><p class="text-sm font-medium text-slate-500">Ingresos por ventas pagadas</p><p class="mt-2 text-2xl font-bold text-emerald-700">{{ $simboloMoneda }}{{ number_format((float) ($resumen['ingresos'] ?? 0), 2, ',', '.') }}</p></x-card>
        <x-card><p class="text-sm font-medium text-slate-500">Inversiones en productos</p><p class="mt-2 text-2xl font-bold text-slate-800">{{ $simboloMoneda }}{{ number_format((float) ($resumen['inversiones'] ?? 0), 2, ',', '.') }}</p></x-card>
        <x-card><p class="text-sm font-medium text-slate-500">Otros gastos</p><p class="mt-2 text-2xl font-bold text-slate-800">{{ $simboloMoneda }}{{ number_format((float) ($resumen['otros_gastos'] ?? 0), 2, ',', '.') }}</p></x-card>
        <x-card><p class="text-sm font-medium text-slate-500">Pérdida por productos dañados</p><p class="mt-2 text-2xl font-bold text-red-700">{{ $simboloMoneda }}{{ number_format((float) ($resumen['perdida_productos'] ?? 0), 2, ',', '.') }}</p></x-card>
        <x-card><p class="text-sm font-medium text-slate-500">Ganancia o pérdida del período</p><p class="mt-2 text-2xl font-bold {{ ($resumen['resultado'] ?? 0) < 0 ? 'text-red-700' : 'text-emerald-700' }}">{{ $simboloMoneda }}{{ number_format((float) ($resumen['resultado'] ?? 0), 2, ',', '.') }}</p></x-card>
        @if (($resumen['presupuesto_final'] ?? null) !== null)
            <x-card><p class="text-sm font-medium text-slate-500">Presupuesto final</p><p class="mt-2 text-2xl font-bold text-slate-800">{{ $simboloMoneda }}{{ number_format((float) $resumen['presupuesto_final'], 2, ',', '.') }}</p></x-card>
        @endif
    </div>

    <x-card title="Ventas pagadas" :padding="false" class="mb-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3">Factura</th><th class="px-5 py-3">Cliente</th><th class="px-5 py-3">Fecha</th><th class="px-5 py-3 text-right">Total</th></tr></thead>
                <tbody class="divide-y divide-slate-100 bg-white text-slate-600">
                    @forelse (($datos['facturas'] ?? []) as $factura)
                        <tr><td class="px-5 py-3 font-medium text-slate-800">{{ $factura['numero'] }}</td><td class="px-5 py-3">{{ $factura['cliente'] }}</td><td class="px-5 py-3">{{ \Illuminate\Support\Carbon::parse($factura['fecha'])->format('d/m/Y') }}</td><td class="px-5 py-3 text-right">{{ $simboloMoneda }}{{ number_format((float) $factura['total'], 2, ',', '.') }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">No hubo ventas pagadas en este período.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    <x-card title="Inversiones y gastos" :padding="false" class="mb-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3">Fecha</th><th class="px-5 py-3">Tipo</th><th class="px-5 py-3">Producto</th><th class="px-5 py-3">Proveedor</th><th class="px-5 py-3">Detalle</th><th class="px-5 py-3 text-right">Monto</th></tr></thead>
                <tbody class="divide-y divide-slate-100 bg-white text-slate-600">
                    @forelse (($datos['movimientos'] ?? []) as $movimiento)
                        <tr><td class="px-5 py-3">{{ \Illuminate\Support\Carbon::parse($movimiento['fecha'])->format('d/m/Y') }}</td><td class="px-5 py-3">{{ $movimiento['tipo'] === 'inversion_producto' ? 'Inversión' : 'Gasto' }}</td><td class="px-5 py-3">{{ $movimiento['producto'] ?? '—' }}</td><td class="px-5 py-3">{{ $movimiento['proveedor'] ?? '—' }}</td><td class="px-5 py-3">{{ $movimiento['descripcion'] }}</td><td class="px-5 py-3 text-right">{{ $simboloMoneda }}{{ number_format((float) $movimiento['monto'], 2, ',', '.') }}</td></tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-8 text-center text-slate-500">No hay inversiones ni gastos registrados en este período.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    <x-card title="Productos dañados o vencidos" :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3">Fecha</th><th class="px-5 py-3">Producto</th><th class="px-5 py-3">Cantidad</th><th class="px-5 py-3">Motivo</th><th class="px-5 py-3 text-right">Pérdida</th></tr></thead>
                <tbody class="divide-y divide-slate-100 bg-white text-slate-600">
                    @forelse (($datos['mermas'] ?? []) as $merma)
                        <tr><td class="px-5 py-3">{{ \Illuminate\Support\Carbon::parse($merma['fecha'])->format('d/m/Y') }}</td><td class="px-5 py-3 font-medium text-slate-800">{{ $merma['producto'] }}</td><td class="px-5 py-3">{{ number_format((float) $merma['cantidad'], 2, ',', '.') }}</td><td class="px-5 py-3">{{ $merma['motivo'] }}</td><td class="px-5 py-3 text-right text-red-700">{{ $simboloMoneda }}{{ number_format((float) $merma['valor_perdida'], 2, ',', '.') }}</td></tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">No hay productos dañados registrados en este período.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
@endsection
