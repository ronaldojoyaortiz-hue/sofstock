<?php

namespace App\Observers;

use App\Models\Auditoria;
use App\Services\AuditoriaService;
use Illuminate\Database\Eloquent\Model;

class AuditoriaObserver
{
    public function __construct(
        protected AuditoriaService $auditoriaService
    ) {}

    /**
     * Registrar automáticamente la creación.
     */
    public function created(Model $model): void
    {
        if ($model instanceof Auditoria) {
            return;
        }

        $this->auditoriaService->registrar(
            accion: 'CREAR',
            modulo: class_basename($model),
            descripcion: 'Se creó un registro en el módulo '
                . class_basename($model) . '.',
            tabla: $model->getTable(),
            idRegistro: $model->getKey(),
            datosNuevos: $this->datosSeguros($model->getAttributes())
        );
    }

    /**
     * Registrar automáticamente las modificaciones.
     */
    public function updated(Model $model): void
    {
        if ($model instanceof Auditoria) {
            return;
        }

        $cambios = $model->getChanges();

        unset(
            $cambios['updated_at'],
            $cambios['created_at']
        );

        if (empty($cambios)) {
            return;
        }

        $anteriores = [];

        foreach (array_keys($cambios) as $campo) {
            $anteriores[$campo] = $model->getRawOriginal($campo);
        }

        $this->auditoriaService->registrar(
            accion: 'ACTUALIZAR',
            modulo: class_basename($model),
            descripcion: 'Se actualizó un registro en el módulo '
                . class_basename($model) . '.',
            tabla: $model->getTable(),
            idRegistro: $model->getKey(),
            datosAnteriores: $this->datosSeguros($anteriores),
            datosNuevos: $this->datosSeguros($cambios)
        );
    }

    /**
     * Registrar automáticamente las eliminaciones.
     */
    public function deleted(Model $model): void
    {
        if ($model instanceof Auditoria) {
            return;
        }

        $this->auditoriaService->registrar(
            accion: 'ELIMINAR',
            modulo: class_basename($model),
            descripcion: 'Se eliminó un registro del módulo '
                . class_basename($model) . '.',
            tabla: $model->getTable(),
            idRegistro: $model->getKey(),
            datosAnteriores: $this->datosSeguros($model->getAttributes())
        );
    }

    /**
     * Evitar guardar contraseñas y tokens.
     */
    private function datosSeguros(array $datos): array
    {
        $camposSensibles = [
            'contrasena',
            'password',
            'password_confirmation',
            'remember_token',
            'token',
        ];

        foreach ($camposSensibles as $campo) {
            unset($datos[$campo]);
        }

        return $datos;
    }
}
