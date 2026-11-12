<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Acta extends Model
{
    protected $fillable = [
        'codigo',
        'tipo',
        'tipo_acta',
        'fecha',
        'hora',
        'lugar',
        'titulo',
        'objetivo',
        'desarrollo',
        'conclusiones',
        'estado',
        'usuario_id',
        'usuario_nombre',
        'rol_id',
        'rol_nombre',
        'inmueble_id',
        'firmante_1_nombre',
        'firmante_1_cargo',
        'firmante_1_usuario_id',
        'firmante_1_rol_id',
        'firmante_1_rol',
        'firmante_2_nombre',
        'firmante_2_cargo',
        'firmante_2_usuario_id',
        'firmante_2_rol_id',
        'firmante_2_rol',
        'productos',
        'firmantes',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'productos' => 'array',
            'firmantes' => 'array',
        ];
    }

    public function firmantesParaMostrar(): array
    {
        if (is_array($this->firmantes) && $this->firmantes !== []) {
            return $this->firmantes;
        }

        return collect([1, 2])
            ->map(fn (int $numero): array => [
                'usuario_id' => $this->{'firmante_'.$numero.'_usuario_id'},
                'rol_id' => $this->{'firmante_'.$numero.'_rol_id'},
                'rol' => $this->{'firmante_'.$numero.'_rol'},
                'nombre' => $this->{'firmante_'.$numero.'_nombre'},
                'cargo' => $this->{'firmante_'.$numero.'_cargo'},
            ])
            ->filter(fn (array $firmante): bool => filled($firmante['nombre']) || filled($firmante['cargo']))
            ->values()
            ->all();
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'id');
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id', 'id_rol');
    }

    public function inmueble(): BelongsTo
    {
        return $this->belongsTo(Inmobiliario::class, 'inmueble_id', 'id_inmueble');
    }

    public function firmante1Usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'firmante_1_usuario_id', 'id');
    }

    public function firmante2Usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'firmante_2_usuario_id', 'id');
    }

    public function firmante1Rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'firmante_1_rol_id', 'id_rol');
    }

    public function firmante2Rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'firmante_2_rol_id', 'id_rol');
    }
}
