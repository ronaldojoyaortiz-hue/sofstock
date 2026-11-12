<?php

namespace App\Services;

use App\Models\Notificacion;
use App\Models\Producto;
use App\Repositories\NotificacionRepository;
use App\Repositories\ProductoRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductoService
{
    public function __construct(
        private ProductoRepository $productoRepository,
        private NotificacionRepository $notificacionRepository
    ) {}

    public function listartodo()
    {
        return $this->productoRepository->listartodo();
    }

    public function guardar(array $datos)
    {
        return DB::transaction(function () use ($datos) {
            $destinatarios = $datos['usuarios_alerta_stock'] ?? null;
            unset($datos['usuarios_alerta_stock']);

            $producto = $this->productoRepository->guardar($datos);

            if ($destinatarios !== null) {
                $producto->usuariosDestinatariosAlertaStock()->sync($destinatarios);
            }

            $this->notificarStockBajo($producto);

            return $producto;
        });
    }

    public function buscar($id)
    {
        return $this->productoRepository->buscar($id);
    }

    public function actualizar($id, array $datos)
    {
        return DB::transaction(function () use ($id, $datos) {
            $productoAnterior = $this->productoRepository->buscar($id);
            $estabaBajoMinimo = $this->estaBajoMinimo($productoAnterior);
            $destinatarios = $datos['usuarios_alerta_stock'] ?? null;
            unset($datos['usuarios_alerta_stock']);

            $producto = $this->productoRepository->actualizar($id, $datos);
            $destinatariosNuevos = [];

            if ($destinatarios !== null) {
                $destinatariosAnteriores = $producto->usuariosDestinatariosAlertaStock()
                    ->pluck('usuarios.id')
                    ->all();
                $producto->usuariosDestinatariosAlertaStock()->sync($destinatarios);
                $destinatariosNuevos = array_values(array_diff($destinatarios, $destinatariosAnteriores));
            }

            if (! $estabaBajoMinimo) {
                $this->notificarStockBajo($producto);
            } elseif ($destinatariosNuevos !== [] && $this->estaBajoMinimo($producto)) {
                $this->notificarStockBajo($producto, $destinatariosNuevos);
            }

            return $producto;
        });
    }

    public function eliminar($id)
    {
        return $this->productoRepository->eliminar($id);
    }

    private function notificarStockBajo(Producto $producto, ?array $soloEstosDestinatarios = null): void
    {
        if (! $this->estaBajoMinimo($producto)) {
            return;
        }

        $consultaDestinatarios = $producto->usuariosDestinatariosAlertaStock()
            ->where('usuarios.activo', true);
        if ($soloEstosDestinatarios !== null) {
            $consultaDestinatarios->whereIn('usuarios.id', $soloEstosDestinatarios);
        }
        $destinatarios = $consultaDestinatarios->pluck('usuarios.id')->all();

        if ($destinatarios === []) {
            return;
        }

        $this->notificacionRepository->crear([
            'usuario_emisor_id' => Auth::id(),
            'id_producto' => $producto->id,
            'titulo' => 'Stock bajo: '.$producto->nombre,
            'mensaje' => "El producto {$producto->nombre} tiene {$producto->stock_actual} unidades; el mínimo configurado es {$producto->stock_minimo}.",
            'tipo' => Notificacion::TIPO_STOCK_BAJO,
            'fecha_evento' => now(),
            'datos' => [
                'stock_actual' => $producto->stock_actual,
                'stock_minimo' => $producto->stock_minimo,
            ],
        ], $destinatarios);
    }

    private function estaBajoMinimo(Producto $producto): bool
    {
        return (float) $producto->stock_minimo > 0
            && (float) $producto->stock_actual <= (float) $producto->stock_minimo;
    }
}
