<?php

namespace App\Http\Controllers;

use App\Models\Inmobiliario;
use App\Services\InmobiliarioService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class InmobiliarioController extends Controller
{
    protected InmobiliarioService $service;

    public function __construct(InmobiliarioService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $inmobiliarios = $this->service->listar();

        return view('inmobiliarios.index', compact('inmobiliarios'));
    }

    public function create()
    {
        return view('inmobiliarios.create');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'codigo' => [
                'required',
                'string',
                'max:30',
                'unique:inmuebles,codigo',
            ],
            'tipo_inmueble' => ['required', 'string', 'max:50'],
            'descripcion' => ['nullable', 'string'],
            'direccion' => ['required', 'string', 'max:255'],
            'ciudad' => ['required', 'string', 'max:100'],
            'area_m2' => ['nullable', 'numeric', 'min:0'],
            'estado' => [
                'required',
                Rule::in([
                    'Disponible',
                    'Ocupado',
                    'Mantenimiento',
                    'Inactivo',
                ]),
            ],
            'observaciones' => ['nullable', 'string'],
            'archivos' => ['nullable', 'array', 'max:10'],
            'archivos.*' => ['file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:10240'],
        ]);

        unset($datos['archivos']);
        $inmobiliario = $this->service->crear($datos);
        $archivos = $this->guardarArchivos($request->file('archivos', []), $inmobiliario->id_inmueble);
        if ($archivos !== []) {
            $inmobiliario->archivos = $archivos;
        }
        if ($archivos !== []) {
            $inmobiliario->save();
        }

        return redirect()
            ->route('inmuebles.index')
            ->with('success', 'Inmobiliario registrado correctamente.');
    }

    public function show(int $id)
    {
        $inmobiliario = $this->service->obtener($id);

        return view('inmobiliarios.show', compact('inmobiliario'));
    }

    public function edit(int $id)
    {
        $inmobiliario = $this->service->obtener($id);

        return view('inmobiliarios.edit', compact('inmobiliario'));
    }

    public function update(Request $request, int $id)
    {
        $inmobiliario = $this->service->obtener($id);

        $datos = $request->validate([
            'codigo' => [
                'required',
                'string',
                'max:30',
                Rule::unique('inmuebles', 'codigo')
                    ->ignore($inmobiliario->id_inmueble, 'id_inmueble'),
            ],
            'tipo_inmueble' => ['required', 'string', 'max:50'],
            'descripcion' => ['nullable', 'string'],
            'direccion' => ['required', 'string', 'max:255'],
            'ciudad' => ['required', 'string', 'max:100'],
            'area_m2' => ['nullable', 'numeric', 'min:0'],
            'estado' => [
                'required',
                Rule::in([
                    'Disponible',
                    'Ocupado',
                    'Mantenimiento',
                    'Inactivo',
                ]),
            ],
            'observaciones' => ['nullable', 'string'],
            'archivos' => ['nullable', 'array', 'max:10'],
            'archivos.*' => ['file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:10240'],
        ]);

        unset($datos['archivos']);
        $archivos = $this->guardarArchivos($request->file('archivos', []), $inmobiliario->id_inmueble);
        if ($archivos !== []) {
            $datos['archivos'] = array_merge($inmobiliario->archivos ?? [], $archivos);
        }
        $this->service->actualizar($id, $datos);

        return redirect()
            ->route('inmuebles.index')
            ->with('success', 'Inmobiliario actualizado correctamente.');
    }

    public function destroy(int $id)
    {
        $this->service->eliminar($id);

        return redirect()
            ->route('inmuebles.index')
            ->with('success', 'Inmobiliario eliminado correctamente.');
    }

    public function archivo(int $inmueble, int $indice)
    {
        $inmobiliario = $this->service->obtener($inmueble);
        $archivo = $inmobiliario->archivos[$indice] ?? null;

        abort_unless(is_array($archivo) && isset($archivo['path'], $archivo['name']), 404);

        $mime = $archivo['mime'] ?? 'application/octet-stream';
        $disposition = str_starts_with($mime, 'image/') || $mime === 'application/pdf'
            ? 'inline'
            : 'attachment';

        return Storage::disk('local')->response($archivo['path'], $archivo['name'], ['Content-Type' => $mime], $disposition);
    }

    /**
     * @param array<int, UploadedFile> $archivos
     * @return array<int, array{path: string, name: string, mime: string}>
     */
    private function guardarArchivos(array $archivos, int $inmueble): array
    {
        $adjuntos = [];

        foreach ($archivos as $archivo) {
            $path = $archivo->store("inmuebles/{$inmueble}/archivos", 'local');
            if ($path === false) {
                throw new RuntimeException('No se pudo guardar uno de los archivos del inmueble.');
            }

            $adjuntos[] = [
                'path' => $path,
                'name' => $archivo->getClientOriginalName(),
                'mime' => $archivo->getMimeType(),
            ];
        }

        return $adjuntos;
    }
}
