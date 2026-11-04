@extends('layouts.app')

@section('title', 'Detalle del acta')
@section('page-title', 'Detalle del acta')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">{{ $acta->titulo }}</h2>
            <p class="text-sm text-gray-500">Código: {{ $acta->codigo }}</p>
        </div>

        <div class="flex flex-wrap gap-2" data-html2canvas-ignore="true">
            <button type="button" onclick="descargarActaPdf()"
                    class="rounded-lg bg-green-700 px-4 py-2 font-medium text-white hover:bg-green-800">
                Descargar PDF
            </button>
            <a href="{{ route('actas.index') }}"
               class="rounded-lg border border-gray-300 px-4 py-2 text-gray-700">
                Volver
            </a>
        </div>
    </div>

    <div id="acta-pdf-content" class="rounded-xl border border-gray-200 bg-white p-6 space-y-5">

        <div class="border-b border-gray-300 pb-4 text-center">
            <h1 class="text-2xl font-bold text-gray-900">{{ $acta->titulo }}</h1>
            <p class="mt-1 text-sm text-gray-600">Acta {{ $acta->codigo }}</p>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <p class="text-sm text-gray-500">Tipo de acta</p>
                <p class="font-medium">
                    {{ [
                        'entrega_bienes' => 'Entrega de bienes e inventario',
                        'junta_directiva' => 'Junta directiva',
                        'asamblea' => 'Reunión o asamblea',
                        'entrega_inmueble' => 'Entrega o devolución de inmueble'
                    ][$acta->tipo] ?? $acta->tipo }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Fecha</p>
                <p class="font-medium">{{ $acta->fecha?->format('d/m/Y') }}</p>
            </div>

            @if(!in_array($acta->tipo, ['entrega_bienes', 'entrega_inmueble'], true))
                <div>
                    <p class="text-sm text-gray-500">Hora</p>
                    <p class="font-medium">{{ $acta->hora ?: 'No registrada' }}</p>
                </div>
            @endif

            <div>
                <p class="text-sm text-gray-500">Lugar</p>
                <p class="font-medium">{{ $acta->lugar ?: 'No registrado' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Responsable del registro</p>
                <p class="font-medium">{{ $acta->usuario_nombre }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Rol al registrar</p>
                <p class="font-medium">{{ $acta->rol_nombre ?: 'No registrado' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Estado</p>
                <p class="font-medium">{{ ucfirst(str_replace('_', ' ', $acta->estado)) }}</p>
            </div>

            @if($acta->inmueble)
                <div class="sm:col-span-2">
                    <p class="text-sm text-gray-500">Inmueble relacionado</p>
                    <p class="font-medium">
                        {{ $acta->inmueble->codigo }} · {{ $acta->inmueble->tipo_inmueble }} ·
                        {{ $acta->inmueble->direccion }}, {{ $acta->inmueble->ciudad }}
                    </p>
                </div>
            @endif
        </div>

        <hr>

        @if($acta->productos->isNotEmpty())
            <div>
                <h3 class="mb-2 font-semibold text-gray-800">Productos relacionados</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left">Producto</th>
                                <th class="px-3 py-2 text-left">Cantidad</th>
                                <th class="px-3 py-2 text-left">Observaciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($acta->productos as $producto)
                                <tr>
                                    <td class="px-3 py-2">{{ $producto->nombre }}</td>
                                    <td class="px-3 py-2">{{ $producto->pivot->cantidad }}</td>
                                    <td class="px-3 py-2">{{ $producto->pivot->observaciones ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div>
            <h3 class="mb-2 font-semibold text-gray-800">Objetivo</h3>
            <p class="whitespace-pre-line text-gray-700">{{ $acta->objetivo ?: 'Sin información.' }}</p>
        </div>

        <div>
            <h3 class="mb-2 font-semibold text-gray-800">Desarrollo</h3>
            <p class="whitespace-pre-line text-gray-700">{{ $acta->desarrollo ?: 'Sin información.' }}</p>
        </div>

        <div>
            <h3 class="mb-2 font-semibold text-gray-800">Conclusiones</h3>
            <p class="whitespace-pre-line text-gray-700">{{ $acta->conclusiones ?: 'Sin información.' }}</p>
        </div>

        <hr>

        <div>
            <h3 class="mb-6 font-semibold text-gray-800">Firmas</h3>
            <div class="grid grid-cols-1 gap-10 sm:grid-cols-2">
                @forelse($acta->firmas as $firma)
                    <div class="pt-10 text-center">
                        <div class="border-t border-gray-500 pt-2">
                            <p class="font-medium">{{ $firma->nombre }}</p>
                            <p class="text-sm text-gray-500">{{ ucfirst($firma->cargo) }}</p>
                            <p class="mt-1 text-xs text-gray-500">Firma</p>
                        </div>
                    </div>
                @empty
                    @foreach([
                        [$acta->firmante_1_nombre, $acta->firmante_1_cargo],
                        [$acta->firmante_2_nombre, $acta->firmante_2_cargo],
                    ] as [$nombre, $cargo])
                        @if($nombre || $cargo)
                            <div class="pt-10 text-center">
                                <div class="border-t border-gray-500 pt-2">
                                    <p class="font-medium">{{ $nombre }}</p>
                                    <p class="text-sm text-gray-500">{{ $cargo }}</p>
                                    <p class="mt-1 text-xs text-gray-500">Firma</p>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endforelse
            </div>
        </div>

        <div class="flex justify-end" data-html2canvas-ignore="true">
            <a href="{{ route('actas.edit', $acta->id) }}"
               class="rounded-lg bg-blue-700 px-5 py-2 text-white hover:bg-blue-800">
                Editar acta
            </a>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" referrerpolicy="no-referrer"></script>
<script>
    function descargarActaPdf() {
        const documento = document.getElementById('acta-pdf-content');
        if (!window.html2pdf || !documento) {
            window.print();
            return;
        }

        html2pdf().set({
            margin: [12, 12, 12, 12],
            filename: 'Acta-{{ preg_replace('/[^A-Za-z0-9_-]/', '-', $acta->codigo) }}.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true, backgroundColor: '#ffffff' },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
            pagebreak: { mode: ['css', 'legacy'] }
        }).from(documento).save();
    }
</script>
@endsection
