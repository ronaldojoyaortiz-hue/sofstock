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
use App\Models\ConfiguracionSistema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
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
        Gate::before(function ($usuario, string $habilidad): ?bool {
            $rolLegado = mb_strtolower(trim($usuario->rol?->nombre_rol ?? '')) === 'administrador';
            $rolSpatie = method_exists($usuario, 'hasRole') && $usuario->hasRole('administrador');

            return ($rolLegado || $rolSpatie) ? true : null;
        });

        Categoria::observe(AuditoriaObserver::class);
        Contrato::observe(AuditoriaObserver::class);
        DetalleFactura::observe(AuditoriaObserver::class);
        Facturas::observe(AuditoriaObserver::class);
        Inmobiliario::observe(AuditoriaObserver::class);
        Producto::observe(AuditoriaObserver::class);
        Proveedores::observe(AuditoriaObserver::class);
        Rol::observe(AuditoriaObserver::class);
        Usuario::observe(AuditoriaObserver::class);

        View::composer(['layouts.app', 'reportes.index', 'reportes.show'], function ($view): void {
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

        View::composer('layouts.partials.navbar', function ($view): void {
            $usuario = Auth::user();
            $noLeidas = 0;
            $notificacionesNavbar = collect();

            if ($usuario && Schema::hasTable('notificaciones') && Schema::hasTable('notificacion_usuario')) {
                $noLeidas = $usuario->notificacionesRecibidas()
                    ->wherePivotNull('leida_at')
                    ->count();

                $notificacionesNavbar = $usuario->notificacionesRecibidas()
                    ->with(['emisor:id,nombre,apellido', 'producto:id,nombre'])
                    ->orderByDesc('notificacion_usuario.created_at')
                    ->limit(5)
                    ->get();
            }

            $view->with(compact('notificacionesNavbar', 'noLeidas'));
        });
    }
}
