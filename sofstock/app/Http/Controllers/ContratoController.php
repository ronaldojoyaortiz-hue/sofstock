<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\Inmobiliario;
use App\Services\ContratoService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use RuntimeException;

class ContratoController extends Controller
{
    private const TIPOS = ['Arrendamiento', 'Compraventa', 'Administración', 'Otro'];
    private const TIPOS_DOCUMENTO = [
        'Tarjeta de identidad',
        'Cédula de ciudadanía',
        'Cédula de extranjería',
        'Pasaporte',
    ];
    private const ESTADOS = ['Vigente', 'Finalizado', 'Cancelado'];

    public function __construct(private ContratoService $contratoService)
    {
    }

    public function all()
    {
        $contratos = Contrato::query()
            ->join('inmuebles', 'contratos.id_inmueble', '=', 'inmuebles.id_inmueble')
            ->select(
                'contratos.*',
                'inmuebles.codigo as inmueble_codigo',
                'inmuebles.direccion as inmueble_direccion',
                'inmuebles.ciudad as inmueble_ciudad'
            )
            ->orderByDesc('contratos.fecha_inicio')
            ->paginate(10);

        return view('contratos.all', compact('contratos'));
    }

    public function createGlobal()
    {
        $inmuebles = Inmobiliario::orderBy('codigo')->get([
            'id_inmueble',
            'codigo',
            'direccion',
            'ciudad',
        ]);

        return view('contratos.select-inmueble', compact('inmuebles'));
    }

    public function selectInmueble(Request $request)
    {
        $validated = $request->validate([
            'id_inmueble' => ['required', 'integer', 'exists:inmuebles,id_inmueble'],
        ]);

        return redirect()->route(
            'inmuebles.contratos.create',
            $validated['id_inmueble']
        );
    }

    public function index(int $inmueble)
    {
        $inmobiliario = Inmobiliario::findOrFail($inmueble);
        $contratos = $this->contratoService->listarPorInmueble($inmueble);

        return view('contratos.index', compact('inmobiliario', 'contratos'));
    }

    public function create(int $inmueble)
    {
        $inmobiliario = Inmobiliario::findOrFail($inmueble);

        return view('contratos.create', [
            'inmobiliario' => $inmobiliario,
            'tipos' => self::TIPOS,
            'tiposDocumento' => self::TIPOS_DOCUMENTO,
            'estados' => self::ESTADOS,
        ]);
    }

    public function store(Request $request, int $inmueble)
    {
        Inmobiliario::findOrFail($inmueble);
        $datos = $this->validar($request);
        unset($datos['archivos']);
        $contrato = $this->contratoService->crear($inmueble, $datos);
        $archivos = $this->guardarArchivos($request->file('archivos', []), $inmueble);
        if ($archivos !== []) {
            $contrato->archivos = $archivos;
            $contrato->save();
        }

        return redirect()
            ->route('inmuebles.contratos.index', $inmueble)
            ->with('success', 'Contrato registrado correctamente.');
    }

    public function show(int $inmueble, int $contrato)
    {
        $inmobiliario = Inmobiliario::findOrFail($inmueble);
        $contrato = $this->contratoService->obtener($inmueble, $contrato);

        return view('contratos.show', compact('inmobiliario', 'contrato'));
    }

    public function edit(int $inmueble, int $contrato)
    {
        $inmobiliario = Inmobiliario::findOrFail($inmueble);
        $contrato = $this->contratoService->obtener($inmueble, $contrato);

        return view('contratos.edit', [
            'inmobiliario' => $inmobiliario,
            'contrato' => $contrato,
            'tipos' => self::TIPOS,
            'tiposDocumento' => self::TIPOS_DOCUMENTO,
            'estados' => self::ESTADOS,
        ]);
    }

    public function update(Request $request, int $inmueble, int $contrato)
    {
        $registro = $this->contratoService->obtener($inmueble, $contrato);
        $datos = $this->validar($request, $registro->id_contrato);
        unset($datos['archivos']);
        $archivos = $this->guardarArchivos($request->file('archivos', []), $inmueble);
        if ($archivos !== []) {
            $datos['archivos'] = array_merge($registro->archivos ?? [], $archivos);
        }
        $this->contratoService->actualizar($inmueble, $contrato, $datos);

        return redirect()
            ->route('inmuebles.contratos.index', $inmueble)
            ->with('success', 'Contrato actualizado correctamente.');
    }

    public function destroy(int $inmueble, int $contrato)
    {
        $this->contratoService->eliminar($inmueble, $contrato);

        return redirect()
            ->route('inmuebles.contratos.index', $inmueble)
            ->with('success', 'Contrato eliminado correctamente.');
    }

    public function archivo(int $inmueble, int $contrato, int $indice)
    {
        $registro = $this->contratoService->obtener($inmueble, $contrato);
        $archivo = $registro->archivos[$indice] ?? null;

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
            $path = $archivo->store("contratos/{$inmueble}/archivos", 'local');
            if ($path === false) {
                throw new RuntimeException('No se pudo guardar uno de los archivos del contrato.');
            }

            $adjuntos[] = [
                'path' => $path,
                'name' => $archivo->getClientOriginalName(),
                'mime' => $archivo->getMimeType(),
            ];
        }

        return $adjuntos;
    }

    private function validar(Request $request, ?int $contratoId = null): array
    {
        $numeroRule = Rule::unique('contratos', 'numero_contrato');
        if ($contratoId !== null) {
            $numeroRule->ignore($contratoId, 'id_contrato');
        }

        return $request->validate([
            'numero_contrato' => ['required', 'string', 'max:50', $numeroRule],
            'tipo_contrato' => ['required', 'string', Rule::in(self::TIPOS)],
            'contraparte' => ['required', 'string', 'max:150'],
            'tipo_documento_contraparte' => ['nullable', 'string', Rule::in(self::TIPOS_DOCUMENTO)],
            'documento_contraparte' => ['nullable', 'string', 'max:50'],
            'archivos' => ['nullable', 'array', 'max:10'],
            'archivos.*' => ['file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:10240'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'valor' => ['nullable', 'numeric', 'min:0'],
            'estado' => ['required', 'string', Rule::in(self::ESTADOS)],
            'observaciones' => ['nullable', 'string'],
        ]);
    }
}
