<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Observers\AuditoriaObserver;
use App\Models\Categoria;
use App\Models\Contrato;
use App\Models\DetalleFactura;
use App\Models\Facturas;
use App\Models\Inmobiliario;
use App\Models\Producto;
use App\Models\Proveedores;
use App\Models\Rol;
use App\Models\Usuario;
use App\Models\User;
use App\Models\ConfiguracionSistema;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Categoria::observe(AuditoriaObserver::class);
        Contrato::observe(AuditoriaObserver::class);
        DetalleFactura::observe(AuditoriaObserver::class);
        Facturas::observe(AuditoriaObserver::class);
        Inmobiliario::observe(AuditoriaObserver::class);
        Producto::observe(AuditoriaObserver::class);
        Proveedores::observe(AuditoriaObserver::class);
        Rol::observe(AuditoriaObserver::class);
        Usuario::observe(AuditoriaObserver::class);
        User::observe(AuditoriaObserver::class);

        View::composer(['layouts.app', 'reportes.show'], function ($view): void {
            $configuracion = Schema::hasTable('configuraciones_sistema')
                ? ConfiguracionSistema::query()->find(1)
                : null;
            app()->setLocale($configuracion?->idioma ?? 'es');
            $view->with('nombreSistema', $configuracion?->nombre_empresa ?? config('app.name', 'SofStock'));
            $view->with('idiomaSistema', $configuracion?->idioma ?? 'es');
            $view->with('tamanoLetra', $configuracion?->tamano_letra ?? 16);
            $view->with('simboloMoneda', match ($configuracion?->moneda ?? 'COP') {
                'USD' => 'US$',
                'EUR' => '€',
                'MXN' => 'MX$',
                default => '$',
            });
        });
    }
}
