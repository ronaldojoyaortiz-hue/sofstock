<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use App\Repositories\NotificacionRepository;
use App\services\NotificacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NotificacionController extends Controller
{
    public function __construct(
        private readonly NotificacionRepository $notificaciones,
        private readonly NotificacionService $servicio
    ) {}

    public function index(Request $request): View
    {
        $usuario = $request->user();

        return view('notificaciones.index', [
            'notificaciones' => $this->notificaciones->listarRecibidas($usuario),
            'noLeidas' => $this->notificaciones->contarNoLeidas($usuario),
        ]);
    }

    public function create(): View
    {
        return view('notificaciones.create', [
            'roles' => $this->notificaciones->rolesDisponibles(),
            'usuarios' => $this->notificaciones->usuariosActivosDisponibles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'titulo' => ['required', 'string', 'max:150'],
            'mensaje' => ['required', 'string', 'max:10000'],
            'fecha_evento' => ['nullable', 'date'],
            'lugar' => ['nullable', 'string', 'max:200'],
            'usuarios' => ['nullable', 'array'],
            'usuarios.*' => [
                'integer',
                Rule::exists('usuarios', 'id')->where('activo', true),
            ],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', Rule::exists('roles', 'id_rol')],
        ]);

        $this->servicio->enviarManual($request->user(), $datos);

        return redirect()
            ->route('notificaciones.index')
            ->with('success', 'La notificación se envió correctamente.');
    }

    public function destroy(Notificacion $notificacion): RedirectResponse
    {
        $this->notificaciones->eliminar($notificacion);

        return redirect()
            ->route('notificaciones.index')
            ->with('success', 'La notificación se eliminó correctamente.');
    }

    public function marcarLeida(Request $request, Notificacion $notificacion): RedirectResponse
    {
        abort_unless(
            $this->notificaciones->marcarComoLeida($request->user(), $notificacion),
            404
        );

        return back()->with('success', 'La notificación se marcó como leída.');
    }
}
