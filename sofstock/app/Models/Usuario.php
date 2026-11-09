<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class Usuario extends Authenticatable
{
    use HasRoles;

    public const TIPOS_DOCUMENTO = [
        'CC',
        'CE',
        'TI',
        'PAS',
        'NIT',
    ];

    public const GENEROS = [
        'Masculino',
        'Femenino',
        'Otro',
    ];

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
        'pregunta_seguridad',
        'respuesta_seguridad',
        'estado_aprobacion',
    ];

    protected $hidden = [
        'contrasena',
        'contrasena_cifrada',
        'respuesta_seguridad',
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

    public function scopeAdministradores(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->whereHas('rol', fn (Builder $role) => $role
                ->whereRaw('LOWER(TRIM(nombre_rol)) = ?', ['administrador']))
                ->orWhereHas('roles', fn (Builder $role) => $role
                    ->where('guard_name', 'web')
                    ->whereRaw('LOWER(TRIM(name)) = ?', ['administrador']));
        });
    }

    public function esAdministrador(): bool
    {
        return mb_strtolower(trim($this->rol?->nombre_rol ?? '')) === 'administrador'
            || $this->hasRole('administrador');
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
