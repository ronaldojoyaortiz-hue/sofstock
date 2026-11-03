<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Facturas;
use App\Models\Producto;
use App\Models\Proveedores;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BusquedaController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $query = trim($validated['q'] ?? '');
        $results = collect();
        $usuario = $request->user();
        $like = '%' . addcslashes($query, '%_\\') . '%';

        if ($query !== '') {
            $terminoNormalizado = Str::lower(Str::ascii($query));
            $modulos = [
                ['alias' => ['usuario', 'usuarios'], 'permiso' => 'ver usuarios', 'tipo' => 'Módulo', 'titulo' => 'Usuarios', 'detalle' => 'Abrir el módulo de usuarios', 'ruta' => 'usuarios.index'],
                ['alias' => ['producto', 'productos', 'inventario'], 'permiso' => 'ver productos', 'tipo' => 'Módulo', 'titulo' => 'Productos', 'detalle' => 'Abrir el módulo de productos', 'ruta' => 'productos.index'],
                ['alias' => ['proveedor', 'proveedores'], 'permiso' => 'ver proveedores', 'tipo' => 'Módulo', 'titulo' => 'Proveedores', 'detalle' => 'Abrir el módulo de proveedores', 'ruta' => 'proveedores.index'],
                ['alias' => ['categoria', 'categorias'], 'permiso' => 'ver categorias', 'tipo' => 'Módulo', 'titulo' => 'Categorías', 'detalle' => 'Abrir el módulo de categorías', 'ruta' => 'categorias.index'],
                ['alias' => ['factura', 'facturas', 'venta', 'ventas'], 'permiso' => 'ver facturas', 'tipo' => 'Módulo', 'titulo' => 'Facturas', 'detalle' => 'Abrir el módulo de facturas y ventas', 'ruta' => 'facturas.index'],
                ['alias' => ['reporte', 'reportes', 'ganancia', 'perdida'], 'permiso' => 'ver reportes', 'tipo' => 'Módulo', 'titulo' => 'Reportes', 'detalle' => 'Abrir el módulo de reportes', 'ruta' => 'reports.index'],
                ['alias' => ['configuracion', 'ajuste', 'ajustes', 'idioma', 'letra', 'fuente', 'apariencia', 'oscuro', 'claro', 'moneda', 'empresa'], 'permiso' => 'ver settings', 'tipo' => 'Módulo', 'titulo' => 'Configuración', 'detalle' => 'Idioma, tamaño de letra, apariencia, moneda y nombre', 'ruta' => 'settings.index'],
                ['alias' => ['inmueble', 'inmuebles'], 'permiso' => 'ver inmuebles', 'tipo' => 'Módulo', 'titulo' => 'Inmuebles', 'detalle' => 'Abrir el módulo de inmuebles', 'ruta' => 'inmuebles.index'],
                ['alias' => ['contrato', 'contratos'], 'permiso' => 'ver contratos', 'tipo' => 'Módulo', 'titulo' => 'Contratos', 'detalle' => 'Abrir el módulo de contratos', 'ruta' => 'contratos.index'],
                ['alias' => ['rol', 'roles', 'permiso', 'permisos', 'seguridad'], 'permiso' => 'gestionar seguridad', 'tipo' => 'Módulo', 'titulo' => 'Permisos y roles', 'detalle' => 'Abrir la gestión de permisos', 'ruta' => 'seguridad.permisos.index'],
                ['alias' => ['auditoria', 'auditorias', 'actividad'], 'permiso' => 'ver auditorias', 'tipo' => 'Módulo', 'titulo' => 'Auditorías', 'detalle' => 'Abrir el módulo de auditorías', 'ruta' => 'auditorias.index'],
            ];

            foreach ($modulos as $modulo) {
                $coincide = collect($modulo['alias'])->contains(fn (string $alias) => Str::contains($terminoNormalizado, $alias));
                $tieneAcceso = $usuario->hasRole('administrador')
                    || $usuario->can($modulo['permiso']);

                if ($coincide && $tieneAcceso && \Illuminate\Support\Facades\Route::has($modulo['ruta'])) {
                    $results->push($this->resultado($modulo['tipo'], $modulo['titulo'], $modulo['detalle'], route($modulo['ruta'])));
                }
            }

            if ($usuario->can('ver productos')) {
                Producto::query()
                    ->where(fn ($builder) => $builder->where('nombre', 'like', $like)
                        ->orWhere('marca', 'like', $like)
                        ->orWhere('descripcion', 'like', $like))
                    ->limit(8)->get()->each(fn (Producto $item) => $results->push($this->resultado(
                        'Producto', $item->nombre, $item->marca ?: 'Producto', route('productos.show', $item->id)
                    )));
            }

            if ($usuario->can('ver categorias')) {
                Categoria::query()
                    ->where(fn ($builder) => $builder->where('nombre', 'like', $like)->orWhere('descripcion', 'like', $like))
                    ->limit(5)->get()->each(fn (Categoria $item) => $results->push($this->resultado(
                        'Categoría', $item->nombre, 'Categorías', route('categorias.show', $item->id_categoria)
                    )));
            }

            if ($usuario->can('ver proveedores')) {
                Proveedores::query()
                    ->where(fn ($builder) => $builder->where('razon_social', 'like', $like)
                        ->orWhere('nit', 'like', $like)->orWhere('contacto_nombre', 'like', $like))
                    ->limit(5)->get()->each(fn (Proveedores $item) => $results->push($this->resultado(
                        'Proveedor', $item->razon_social, 'NIT: ' . $item->nit, route('proveedores.show', $item->id_proveedor)
                    )));
            }

            if ($usuario->can('ver usuarios')) {
                Usuario::query()
                    ->where(fn ($builder) => $builder->where('nombre', 'like', $like)
                        ->orWhere('apellido', 'like', $like)->orWhere('correo', 'like', $like)->orWhere('documento', 'like', $like))
                    ->limit(5)->get()->each(fn (Usuario $item) => $results->push($this->resultado(
                        'Usuario', trim($item->nombre . ' ' . $item->apellido), $item->correo, route('usuarios.show', $item->id)
                    )));
            }

            if ($usuario->can('ver facturas')) {
                Facturas::query()
                    ->where(fn ($builder) => $builder->where('numero', 'like', $like)
                        ->orWhere('cliente_nombre', 'like', $like)->orWhere('cliente_documento', 'like', $like))
                    ->limit(5)->get()->each(fn (Facturas $item) => $results->push($this->resultado(
                        'Factura', $item->numero ?: 'Factura #' . $item->id_factura, $item->cliente_nombre, route('facturas.show', $item)
                    )));
            }

            if (($usuario->can('ver settings') || $usuario->hasRole('administrador'))
                && collect(['configuracion', 'ajustes', 'idioma', 'letra', 'fuente', 'apariencia', 'oscuro', 'claro', 'moneda', 'empresa'])
                    ->contains(fn (string $palabra) => Str::contains($terminoNormalizado, $palabra))) {
                $results->prepend($this->resultado('Configuración', 'Ajustes del sistema', 'Idioma, tamaño de letra, apariencia, moneda y nombre', route('settings.index')));
            }
        }

        return view('buscar.index', compact('query', 'results'));
    }

    private function resultado(string $tipo, string $titulo, ?string $detalle, string $url): object
    {
        return (object) compact('tipo', 'titulo', 'detalle', 'url');
    }
}
