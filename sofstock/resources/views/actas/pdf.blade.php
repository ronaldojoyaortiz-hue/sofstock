@php
    $tiposActa = [
        'entrega' => 'Acta de entrega',
        'recibo' => 'Acta de recibo',
        'junta_directiva' => 'Junta directiva',
        'asamblea' => 'Asamblea',
        'inventario' => 'Acta de inventario',
        'devolucion' => 'Acta de devolución',
    ];
    $nombreArchivo = 'Acta-' . preg_replace('/[^A-Za-z0-9_-]/', '-', $acta->codigo) . '.pdf';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $nombreArchivo }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #eef2f7;
            color: #111827;
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.5;
        }

        .toolbar {
            display: flex;
            justify-content: center;
            gap: 12px;
            padding: 18px;
        }

        .toolbar button {
            border: 0;
            border-radius: 6px;
            background: #166534;
            color: #fff;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            padding: 10px 18px;
        }

        .paper {
            width: 186mm;
            min-height: 273mm;
            margin: 0 auto 24px;
            padding: 16mm 15mm;
            background: #fff;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            border-bottom: 2px solid #1d4ed8;
            margin-bottom: 22px;
            padding-bottom: 14px;
        }

        .logo {
            max-width: 150px;
            max-height: 58px;
            object-fit: contain;
        }

        .header-text {
            flex: 1;
            text-align: right;
        }

        .header-text h1 {
            margin: 0;
            color: #1e3a8a;
            font-size: 18pt;
        }

        .header-text p,
        .muted {
            color: #64748b;
        }

        .header-text p {
            margin: 3px 0 0;
        }

        .metadata {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px 20px;
            margin: 18px 0 22px;
        }

        .field {
            overflow-wrap: anywhere;
        }

        .field strong {
            display: block;
            margin-bottom: 2px;
            color: #475569;
            font-size: 9pt;
            text-transform: uppercase;
        }

        h2 {
            margin: 20px 0 8px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 5px;
            color: #1e3a8a;
            font-size: 12pt;
            page-break-after: avoid;
        }

        .text-block {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 7px;
            text-align: left;
            vertical-align: top;
            overflow-wrap: anywhere;
        }

        th {
            background: #eff6ff;
            color: #1e3a8a;
        }

        tr,
        .signature {
            page-break-inside: avoid;
        }

        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 34px;
            margin-top: 48px;
        }

        .signature {
            min-height: 92px;
            padding-top: 42px;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #475569;
            padding-top: 7px;
        }

        .signature p {
            margin: 0;
        }

        .signature .muted {
            font-size: 9pt;
        }

        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        @media print {
            body {
                background: #fff;
            }

            .toolbar {
                display: none;
            }

            .paper {
                width: auto;
                min-height: 0;
                margin: 0;
                padding: 0;
            }
        }

        @media screen and (max-width: 760px) {
            .paper {
                width: 100%;
                min-height: 0;
                padding: 24px;
            }

            .metadata,
            .signatures {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="descargarActaPdf()">Descargar PDF</button>
    </div>

    <main id="acta-pdf-content" class="paper">
        <header class="header">
            <img class="logo" src="{{ asset('images/sofstock-logo.png') }}" alt="Logo de Sofstock">
            <div class="header-text">
                <h1>{{ $acta->titulo }}</h1>
                <p>{{ $tiposActa[$acta->tipo_acta] ?? $acta->tipo_acta }} · Código {{ $acta->codigo }}</p>
            </div>
        </header>

        <section class="metadata">
            <div class="field"><strong>Tipo de acta</strong>{{ $tiposActa[$acta->tipo_acta] ?? $acta->tipo_acta }}</div>
            <div class="field"><strong>Estado</strong>{{ ucfirst(str_replace('_', ' ', $acta->estado)) }}</div>
            <div class="field"><strong>Fecha</strong>{{ $acta->fecha?->format('d/m/Y') ?: 'No registrada' }}</div>
            <div class="field"><strong>Hora</strong>{{ $acta->hora ?: 'No registrada' }}</div>
            <div class="field"><strong>Lugar</strong>{{ $acta->lugar ?: 'No registrado' }}</div>
            <div class="field"><strong>Responsable del registro</strong>{{ $acta->usuario_nombre ?: 'No registrado' }}</div>
            <div class="field"><strong>Rol del responsable</strong>{{ $acta->rol_nombre ?: 'No registrado' }}</div>
            @if($acta->inmueble)
                <div class="field">
                    <strong>Inmueble relacionado</strong>
                    {{ $acta->inmueble->codigo }} · {{ $acta->inmueble->tipo_inmueble }} ·
                    {{ $acta->inmueble->direccion }}, {{ $acta->inmueble->ciudad }}
                </div>
            @endif
        </section>

        <section>
            <h2>Objetivo</h2>
            <div class="text-block">{{ $acta->objetivo ?: 'Sin información.' }}</div>
        </section>

        <section>
            <h2>Desarrollo</h2>
            <div class="text-block">{{ $acta->desarrollo ?: 'Sin información.' }}</div>
        </section>

        <section>
            <h2>Productos relacionados</h2>
            @if(count($acta->productos ?? []))
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($acta->productos as $producto)
                            <tr>
                                <td>{{ $producto['nombre'] ?? 'Producto sin nombre' }}</td>
                                <td>{{ $producto['cantidad'] ?? '—' }}</td>
                                <td>{{ $producto['observaciones'] ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="muted">No hay productos relacionados.</p>
            @endif
        </section>

        <section>
            <h2>Conclusiones</h2>
            <div class="text-block">{{ $acta->conclusiones ?: 'Sin información.' }}</div>
        </section>

        <section>
            <h2>Firmas</h2>
            <div class="signatures">
                @foreach($acta->firmantesParaMostrar() as $firmante)
                    <div class="signature">
                        <div class="signature-line">
                            <p>{{ $firmante['nombre'] ?? ' ' }}</p>
                            <p>{{ $firmante['cargo'] ?? $firmante['rol'] ?? 'Cargo no registrado' }}</p>
                            <p class="muted">Firma</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" referrerpolicy="no-referrer"></script>
    <script>
        function descargarActaPdf() {
            const documento = document.getElementById('acta-pdf-content');
            if (!window.html2pdf || !documento) {
                alert('No se pudo cargar el generador de PDF. Se abrirá la impresión del navegador.');
                window.print();
                return;
            }

            html2pdf().set({
                margin: [12, 12, 12, 12],
                filename: @json($nombreArchivo),
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true, backgroundColor: '#ffffff' },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
                pagebreak: { mode: ['css', 'legacy'], avoid: ['tr', '.signature'] }
            }).from(documento).save().catch((error) => {
                console.error('Error al generar el PDF del acta:', error);
                alert('No se pudo generar el PDF. Intenta nuevamente o usa la opción de impresión.');
            });
        }

        window.addEventListener('load', descargarActaPdf, { once: true });
    </script>
</body>
</html>
