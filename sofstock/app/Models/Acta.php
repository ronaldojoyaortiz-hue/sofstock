<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Acta extends Model
{
    protected $fillable = [
        'codigo',
        'tipo',
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
        'firmante_2_nombre',
        'firmante_2_cargo',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
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

    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'acta_producto', 'acta_id', 'producto_id')
            ->withPivot(['cantidad', 'observaciones'])
            ->withTimestamps();
    }

    public function firmas(): HasMany
    {
        return $this->hasMany(ActaFirma::class);
    }
}
