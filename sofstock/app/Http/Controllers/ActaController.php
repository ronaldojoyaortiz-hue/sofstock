<?php

namespace App\Http\Controllers;

use App\Models\Acta;
use App\Models\Inmobiliario;
use App\Models\Producto;
use App\Models\Rol;
use App\Services\ActaService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ActaController extends Controller
{
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
        return view('actas.create', [
            'productos' => Producto::query()->where('activo', true)->orderBy('nombre')->get(['id', 'nombre', 'stock_actual']),
            'inmuebles' => Inmobiliario::query()->orderBy('codigo')->get(['id_inmueble', 'codigo', 'tipo_inmueble', 'direccion', 'ciudad']),
            'rolesFirmantes' => $this->rolesFirmantes(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo' => ['required', 'string', 'max:50', 'unique:actas,codigo'],
            'tipo' => [
                'required',
                Rule::in([
                    'entrega_bienes',
                    'junta_directiva',
                    'asamblea',
                    'entrega_inmueble'
                ])
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
                    'anulada'
                ])
            ],
            'inmueble_id' => ['nullable', 'required_if:tipo,entrega_inmueble', 'exists:inmuebles,id_inmueble'],
            'productos' => ['exclude_unless:tipo,entrega_bienes', 'required', 'array', 'min:1'],
            'productos.*.producto_id' => ['required', 'integer', 'distinct', Rule::exists('productos', 'id')->where('activo', true)],
            'productos.*.cantidad' => ['required', 'numeric', 'gt:0'],
            'productos.*.observaciones' => ['nullable', 'string', 'max:500'],
            'rol_firmantes_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id_rol')->where(
                    fn ($query) => $query->whereRaw('LOWER(nombre_rol) NOT IN (?, ?)', ['presidente', 'secretaria'])
                ),
            ],
        ]);

        if (in_array($data['tipo'], ['entrega_bienes', 'entrega_inmueble'], true)) {
            $data['hora'] = null;
        }
        if ($data['tipo'] !== 'entrega_inmueble') {
            $data['inmueble_id'] = null;
        }

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

    public function edit(Acta $acta)
    {
        return view('actas.edit', [
            'acta' => $acta->load(['productos', 'firmas']),
            'productos' => Producto::query()->where('activo', true)->orderBy('nombre')->get(['id', 'nombre', 'stock_actual']),
            'inmuebles' => Inmobiliario::query()->orderBy('codigo')->get(['id_inmueble', 'codigo', 'tipo_inmueble', 'direccion', 'ciudad']),
            'rolesFirmantes' => $this->rolesFirmantes(),
        ]);
    }

    public function update(Request $request, Acta $acta)
    {
        $data = $request->validate([
            'tipo' => [
                'required',
                Rule::in([
                    'entrega_bienes',
                    'junta_directiva',
                    'asamblea',
                    'entrega_inmueble'
                ])
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
                    'anulada'
                ])
            ],
            'inmueble_id' => ['nullable', 'required_if:tipo,entrega_inmueble', 'exists:inmuebles,id_inmueble'],
            'productos' => ['exclude_unless:tipo,entrega_bienes', 'required', 'array', 'min:1'],
            'productos.*.producto_id' => ['required', 'integer', 'distinct', Rule::exists('productos', 'id')->where('activo', true)],
            'productos.*.cantidad' => ['required', 'numeric', 'gt:0'],
            'productos.*.observaciones' => ['nullable', 'string', 'max:500'],
            'rol_firmantes_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id_rol')->where(
                    fn ($query) => $query->whereRaw('LOWER(nombre_rol) NOT IN (?, ?)', ['presidente', 'secretaria'])
                ),
            ],
        ]);

        if (in_array($data['tipo'], ['entrega_bienes', 'entrega_inmueble'], true)) {
            $data['hora'] = null;
        }
        if ($data['tipo'] !== 'entrega_inmueble') {
            $data['inmueble_id'] = null;
        }

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

    private function rolesFirmantes()
    {
        return Rol::query()
            ->whereRaw('LOWER(nombre_rol) NOT IN (?, ?)', ['presidente', 'secretaria'])
            ->with(['usuarios' => fn ($query) => $query
                ->where('activo', true)
                ->orderBy('apellido')
                ->orderBy('nombre')])
            ->orderBy('nombre_rol')
            ->get(['id_rol', 'nombre_rol']);
    }
}
