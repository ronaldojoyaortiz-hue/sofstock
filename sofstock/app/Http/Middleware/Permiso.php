<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Permiso
{
    private const ACTION_PERMISSIONS = [
        'index' => 'ver',
        'show' => 'ver',
        'all' => 'ver',
        'archivo' => 'ver',
        'create' => 'crear',
        'store' => 'crear',
        'createGlobal' => 'crear',
        'selectInmueble' => 'crear',
        'edit' => 'editar',
        'update' => 'editar',
        'destroy' => 'eliminar',
        'restablecerContrasena' => 'editar',
    ];

    public function handle(
        Request $request,
        Closure $next,
        string $modulo,
        ?string $accion = null
    ) {
        $metodo = $request->route()?->getActionMethod();

        $usuario = $request->user();

        $nombreRol = mb_strtolower(trim($usuario?->rol?->nombre_rol ?? ''));
        if ($nombreRol === 'administrador' || $usuario?->hasRole('administrador')) {
            return $next($request);
        }

        abort_unless(
            $usuario?->can("acceder {$modulo}"),
            403
        );

        if (
            $modulo === 'usuarios' &&
            in_array($metodo, ['show', 'edit', 'update'], true)
        ) {
            $id = $request->route('usuario')
                ?? $request->route('id');

            if (is_object($id)) {
                $id = $id->getKey();
            }

            abort_unless(
                (string) $id ===
                    (string) $usuario?->getAuthIdentifier(),
                403
            );
        }

        $accion ??= self::ACTION_PERMISSIONS[$metodo] ?? null;

        abort_if($accion === null, 403);

        abort_unless(
            $usuario?->can("{$accion} {$modulo}"),
            403
        );

        if ($metodo === 'edit') {
            abort_unless(
                $usuario?->can("ver {$modulo}"),
                403
            );
        }

        return $next($request);
    }
}
