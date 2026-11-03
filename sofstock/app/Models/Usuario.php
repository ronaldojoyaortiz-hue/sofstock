<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class Usuario extends Authenticatable
{
    use HasRoles;

    protected $table = 'usuarios';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'apellido',
        'correo',
        'telefono',
        'direccion',
        'genero',
        'genero_detalle',
        'tipo_documento',
        'documento',
        'contrasena',
        'contrasena_cifrada',
        'id_rol',
        'activo',
        'fecha_registro',
        'fecha_actualizacion',
    ];

    protected $hidden = [
        'contrasena',
        'contrasena_cifrada',
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

    public function auditorias(): HasMany
    {
        return $this->hasMany(Auditoria::class, 'id_usuario', 'id');
    }

    public function notificacionesEnviadas(): HasMany
    {
        return $this->hasMany(Notificacion::class, 'usuario_emisor_id', 'id');
    }

    public function notificacionesRecibidas(): BelongsToMany
    {
        return $this->belongsToMany(
            Notificacion::class,
            'notificacion_usuario',
            'id_usuario',
            'id_notificacion',
            'id',
            'id'
        )->withPivot('leida_at')->withTimestamps();
    }
}
