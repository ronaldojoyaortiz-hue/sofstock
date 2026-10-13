<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Usuario extends Authenticatable
{
    protected $table = 'usuarios';

    protected $primaryKey = 'id';

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

    protected $hidden = [
        'contrasena',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_registro' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    public function getAuthPasswordName(): string
    {
        return 'contrasena';
    }

    public function getAuthPassword(): string
    {
        return $this->contrasena;
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }
}
