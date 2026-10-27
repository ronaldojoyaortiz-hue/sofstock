<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Observers\AuditoriaObserver;
use App\Models\Categoria;
use App\Models\DetalleFactura;
use App\Models\Facturas;
use App\Models\Producto;
use App\Models\Proveedores;
use App\Models\Rol;
use App\Models\Usuario;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Categoria::observe(AuditoriaObserver::class);
        DetalleFactura::observe(AuditoriaObserver::class);
        Facturas::observe(AuditoriaObserver::class);
        Producto::observe(AuditoriaObserver::class);
        Proveedores::observe(AuditoriaObserver::class);
        Rol::observe(AuditoriaObserver::class);
        Usuario::observe(AuditoriaObserver::class);
        User::observe(AuditoriaObserver::class);
    }
}
