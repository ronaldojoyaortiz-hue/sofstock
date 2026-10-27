<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Proveedores;
use App\Services\ProductoService;

class ProductoController extends Controller
{
    public function __construct(private ProductoService $productoService) {}
    /**
     * Mostrar el listado de recursos.
     */
    public function index()
    {
        $productos = Producto::with('categoria')->get();
        return view('productos.index', compact('productos'));
    }

    /**
     * Mostrar el formulario para crear un recurso.
     */
    public function create()
    {
        $categorias = Categoria::where('activa', true)->get();
        $proveedores = Proveedores::orderBy('razon_social')->get();
        return view('productos.create', compact('categorias', 'proveedores'));
    }

    /**
     * Guardar un recurso recién creado.
     */
    public function store(Request $request)
    {
        $this->productoService->guardar($this->validarProducto($request));

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto creado exitosamente.');
    }

    /**
     * Mostrar el recurso indicado.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Mostrar el formulario para editar el recurso indicado.
     */
    public function edit(string $id)
    {
        $producto = $this->productoService->buscar($id);
        $categorias = Categoria::where('activa', true)->get();
        $proveedores = Proveedores::orderBy('razon_social')->get();
        return view('productos.edit', compact('producto', 'categorias', 'proveedores'));
    }

    /**
     * Actualizar el recurso indicado.
     */
    public function update(Request $request, string $id)
    {
        $this->productoService->actualizar($id, $this->validarProducto($request));

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto actualizado exitosamente.');
    }

    /**
     * Eliminar el recurso indicado.
     */
    public function destroy(string $id)
    {
        $this->productoService->eliminar($id);

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto eliminado exitosamente.');
    }

    private function validarProducto(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'marca' => ['nullable', 'string', 'max:100'],
            'id_categoria' => ['required', 'integer', 'exists:categorias,id_categoria'],
            'id_proveedor' => ['nullable', 'integer', 'exists:proveedores,id_proveedor'],
            'stock_actual' => ['required', 'integer', 'min:0'],
            'precio_base' => ['required', 'numeric', 'min:0'],
            'activo' => ['required', 'boolean'],
        ]);
    }
}
