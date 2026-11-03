<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionSistema;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ConfiguracionController extends Controller
{
    public function index(): View
    {
        $tablaDisponible = Schema::hasTable('configuraciones_sistema');
        $configuracion = $tablaDisponible
            ? ConfiguracionSistema::query()->find(1)
            : null;

        return view('configuracion.index', [
            'tablaDisponible' => $tablaDisponible,
            'configuracion' => $configuracion ?? new ConfiguracionSistema([
                'nombre_empresa' => config('app.name', 'SofStock'),
                'moneda' => 'COP',
                'idioma' => 'es',
                'tamano_letra' => 16,
            ]),
            'idiomas' => ['es' => 'Español', 'en' => 'English'],
            'tamanosLetra' => [14 => 'Pequeña', 16 => 'Normal', 18 => 'Grande'],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        if (! Schema::hasTable('configuraciones_sistema')) {
            return redirect()->route('settings.index')
                ->with('error', 'Aplica las migraciones pendientes para guardar la configuración.');
        }

        $datos = $request->validate([
            'nombre_empresa' => ['required', 'string', 'max:120'],
            'idioma' => ['required', Rule::in(['es', 'en'])],
            'tamano_letra' => ['required', Rule::in([14, 16, 18])],
        ]);

        ConfiguracionSistema::query()->updateOrCreate(['id' => 1], $datos);

        return redirect()->route('settings.index')
            ->with('success', 'La configuración se guardó correctamente.');
    }
}
