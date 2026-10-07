<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Usuario extends Model
{
    protected $table = 'usuarios';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'apellido',
        'correo',
        'contrasena',
        'id_rol',
        'activo',
        'fecha_registro',
        'fecha_actualizacion'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_registro' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }
}
