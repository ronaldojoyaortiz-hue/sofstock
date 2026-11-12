<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SolicitudRegistroController extends Controller
{
    public function index(Request $request): View
    {
        $this->autorizarAdministrador($request);

        $solicitudes = Usuario::query()
            ->with('rol')
            ->where('estado_aprobacion', 'pendiente')
            ->orderBy('fecha_registro')
            ->paginate(15);
        $roles = Rol::query()
            ->whereRaw('LOWER(TRIM(nombre_rol)) NOT IN (?, ?, ?)', ['administrador', 'pendiente', 'secretaria'])
            ->orderBy('nombre_rol')
            ->get();

        return view('auth.registration-requests', compact('solicitudes', 'roles'));
    }

    public function approve(Request $request, int $usuario): RedirectResponse
    {
        $this->autorizarAdministrador($request);

        $validated = $request->validate([
            'id_rol' => [
                'required',
                'integer',
                Rule::exists('roles', 'id_rol')->where(
                    fn ($query) => $query->whereRaw(
                        'LOWER(TRIM(nombre_rol)) NOT IN (?, ?, ?)',
                        ['administrador', 'pendiente', 'secretaria']
                    )
                ),
            ],
        ]);

        $solicitud = DB::transaction(function () use ($usuario, $validated): Usuario {
            $solicitud = Usuario::query()->lockForUpdate()->findOrFail($usuario);
            abort_unless($solicitud->estado_aprobacion === 'pendiente', 404);

            $solicitud->id_rol = $validated['id_rol'];
            $solicitud->estado_aprobacion = 'aprobado';
            $solicitud->activo = true;
            $solicitud->fecha_actualizacion = now();
            $solicitud->save();

            return $solicitud;
        });

        return redirect()->route('registration-requests.index')
            ->with('success', 'La cuenta fue aprobada y ya puede iniciar sesión.');
    }

    public function reject(Request $request, int $usuario): RedirectResponse
    {
        $this->autorizarAdministrador($request);

        DB::transaction(function () use ($usuario): void {
            $solicitud = Usuario::query()->lockForUpdate()->findOrFail($usuario);
            abort_unless($solicitud->estado_aprobacion === 'pendiente', 404);

            $solicitud->estado_aprobacion = 'rechazado';
            $solicitud->activo = false;
            $solicitud->fecha_actualizacion = now();
            $solicitud->save();
        });

        return redirect()->route('registration-requests.index')
            ->with('success', 'La solicitud fue rechazada.');
    }

    private function autorizarAdministrador(Request $request): void
    {
        abort_unless($request->user()?->esAdministrador(), 403);
    }
}
