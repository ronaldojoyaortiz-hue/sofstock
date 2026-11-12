<?php

namespace App\Http\Controllers;

use App\Models\Acta;
use App\Models\Inmobiliario;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\ActaService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ActaController extends Controller
{
    private const TIPOS_ACTA = [
        'entrega',
        'junta_directiva',
        'asamblea',
        'inventario',
        'devolucion',
    ];

    public function __construct(
        protected ActaService $actaService
    ) {}

    public function index()
    {
        $actas = $this->actaService->listar();

        return view('actas.index', compact('actas'));
    }

    public function create()
    {
        $rolesFirmantes = Rol::query()
            ->orderBy('nombre_rol')
            ->get(['id_rol', 'nombre_rol']);

        $usuariosFirmantes = $this->usuariosFirmantes();

        return view('actas.create', [
            'acta' => null,
            'esEdicion' => false,
            'productos' => Producto::query()
                ->where('activo', true)
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'stock_actual']),
            'inmuebles' => Inmobiliario::query()
                ->orderBy('codigo')
                ->get([
                    'id_inmueble',
                    'codigo',
                    'tipo_inmueble',
                    'direccion',
                    'ciudad'
                ]),
            'usuariosFirmantes' => $usuariosFirmantes,
            'rolesFirmantes' => $rolesFirmantes,
            'rolesFirmantesIniciales' => old('roles_firmantes', []),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validarActa($request);

        $this->actaService->registrar($data);

        return redirect()
            ->route('actas.index')
            ->with('success', 'Acta registrada correctamente.');
    }

    public function show(Acta $acta)
    {
        $acta = $this->actaService->consultar($acta->id);

        return view('actas.show', compact('acta'));
    }

    public function pdf(Acta $acta)
    {
        $acta = $this->actaService->consultar($acta->id);

        return view('actas.pdf', compact('acta'));
    }

    public function actualizarEstado(Request $request, Acta $acta)
    {
        $data = $request->validate([
            'estado' => [
                'required',
                Rule::in([
                    'borrador',
                    'pendiente_aprobacion',
                    'aprobada',
                    'firmada',
                    'anulada',
                ]),
            ],
        ]);

        $this->actaService->cambiarEstado($acta, $data['estado']);

        return redirect()
            ->route('actas.show', $acta)
            ->with('success', 'Estado del acta actualizado correctamente.');
    }

    public function edit(Acta $acta)
    {
        $usuariosFirmantes = $this->usuariosFirmantes();

        $rolesFirmantes = Rol::query()
            ->orderBy('nombre_rol')
            ->get(['id_rol', 'nombre_rol']);

        return view('actas.edit', [
            'acta' => $acta->load([
                'firmante1Usuario',
                'firmante2Usuario'
            ]),
            'productos' => Producto::query()
                ->where('activo', true)
                ->orWhereIn(
                    'id',
                    collect($acta->productos)->pluck('producto_id')
                )
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'stock_actual']),
            'inmuebles' => Inmobiliario::query()
                ->orderBy('codigo')
                ->get([
                    'id_inmueble',
                    'codigo',
                    'tipo_inmueble',
                    'direccion',
                    'ciudad'
                ]),
            'usuariosFirmantes' => $usuariosFirmantes,
            'rolesFirmantes' => $rolesFirmantes,
        ]);
    }

    public function update(Request $request, Acta $acta)
    {
        $data = $this->validarActa($request, $acta);

        $this->actaService->actualizar($acta, $data);

        return redirect()
            ->route('actas.index')
            ->with('success', 'Acta actualizada correctamente.');
    }

    public function destroy(Acta $acta)
    {
        $this->actaService->eliminar($acta);

        return redirect()
            ->route('actas.index')
            ->with('success', 'Acta eliminada correctamente.');
    }

    private function validarActa(Request $request, ?Acta $acta = null): array
    {
        $tipoActa = $request->input('tipo_acta');

        if (in_array($tipoActa, ['entrega', 'inventario'], true)) {
            $productos = collect($request->input('productos', []))
                ->filter(fn($producto): bool => filled($producto['producto_id'] ?? null))
                ->values()
                ->all();

            $request->merge(['productos' => $productos]);
        } else {
            $request->merge(['productos' => []]);
        }

        $data = $request->validate([
            'codigo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('actas', 'codigo')->ignore($acta?->id),
            ],
            'tipo_acta' => [
                'required',
                Rule::in(
                    $acta?->tipo_acta === 'recibo'
                        ? [...self::TIPOS_ACTA, 'recibo']
                        : self::TIPOS_ACTA
                ),
            ],
            'fecha' => ['required', 'date'],
            'hora' => ['nullable', 'date_format:H:i'],
            'lugar' => ['nullable', 'string', 'max:255'],
            'titulo' => ['required', 'string', 'max:255'],
            'objetivo' => ['nullable', 'string'],
            'desarrollo' => ['nullable', 'string'],
            'conclusiones' => ['nullable', 'string'],
            'estado' => [
                'required',
                Rule::in([
                    'borrador',
                    'pendiente_aprobacion',
                    'aprobada',
                    'firmada',
                    'anulada',
                ]),
            ],
            'inmueble_id' => [
                'nullable',
                'required_if:tipo_acta,devolucion',
                'exists:inmuebles,id_inmueble'
            ],
            'productos' => [
                'exclude_unless:tipo_acta,entrega,inventario',
                $tipoActa === 'inventario' ? 'required' : 'nullable',
                'array',
                $tipoActa === 'inventario' ? 'min:1' : 'min:0',
            ],
            'productos.*.producto_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('productos', 'id')->where(function ($query) use ($acta): void {
                    $query->where('activo', true);

                    if ($acta) {
                        $query->orWhereIn(
                            'id',
                            collect($acta->productos)->pluck('producto_id')
                        );
                    }
                }),
            ],
            'productos.*.cantidad' => ['required', 'numeric', 'gt:0'],
            'productos.*.observaciones' => ['nullable', 'string', 'max:500'],
            'roles_firmantes' => ['nullable', 'array'],
            'roles_firmantes.*' => [
                'required',
                'integer',
                'distinct',
                'exists:roles,id_rol'
            ],
        ]);

        return $data;
    }

    private function usuariosFirmantes()
    {
        return Usuario::query()
            ->where('activo', true)
            ->with('rol')
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get([
                'id',
                'nombre',
                'apellido',
                'id_rol'
            ])
            ->map(fn(Usuario $usuario): array => [
                'id' => $usuario->id,
                'id_rol' => (int) $usuario->id_rol,
                'nombre' => trim($usuario->nombre . ' ' . $usuario->apellido),
                'cargo' => $usuario->rol?->nombre_rol ?? 'Sin cargo',
            ])
            ->values()
            ->all();
    }
}
