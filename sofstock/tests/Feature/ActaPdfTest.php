<?php

namespace Tests\Feature;

use App\Models\Acta;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ActaPdfTest extends TestCase
{
    public function test_acta_pdf_view_includes_stored_acta_data_logo_and_signature_space(): void
    {
        $acta = Acta::make([
            'codigo' => 'ACTA-PDF-01',
            'tipo_acta' => 'entrega',
            'fecha' => '2026-11-11',
            'hora' => '10:30:00',
            'lugar' => 'Sala principal',
            'titulo' => 'Entrega de mobiliario',
            'objetivo' => 'Formalizar la entrega.',
            'desarrollo' => 'Se revisaron los productos.',
            'conclusiones' => 'La entrega queda registrada.',
            'estado' => 'firmada',
            'usuario_nombre' => 'Responsable de prueba',
            'rol_nombre' => 'Administrador',
            'firmante_1_nombre' => 'Ana Pérez',
            'firmante_1_cargo' => 'Presidenta',
            'firmante_2_nombre' => 'Luis Ruiz',
            'firmante_2_cargo' => 'Administrador',
            'productos' => [
                [
                    'producto_id' => 7,
                    'nombre' => 'Mesa',
                    'cantidad' => 3,
                    'observaciones' => 'Sin daños visibles.',
                ],
            ],
        ]);
        $acta->setRelation('inmueble', null);

        $html = view('actas.pdf', compact('acta'))->render();

        foreach ([
            'sofstock-logo.png',
            'size: A4 portrait',
            'width: 186mm',
            'font-family: "Times New Roman", Times, serif',
            'Entrega de mobiliario',
            'Formalizar la entrega.',
            'Se revisaron los productos.',
            'La entrega queda registrada.',
            'Mesa',
            'Sin daños visibles.',
            'Ana Pérez',
            'Presidenta',
            'Luis Ruiz',
            'Administrador',
            'Acta-ACTA-PDF-01.pdf',
            'html2pdf',
        ] as $contenidoEsperado) {
            $this->assertStringContainsString($contenidoEsperado, $html);
        }
    }

    public function test_acta_pdf_route_requires_the_actas_view_permission(): void
    {
        $route = Route::getRoutes()->getByName('actas.pdf');

        $this->assertNotNull($route);
        $this->assertContains('permiso:actas,ver', $route->gatherMiddleware());
    }
}
