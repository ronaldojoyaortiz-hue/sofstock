<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Auditoria extends Model
{
    protected $table = 'auditorias';

    protected $primaryKey = 'id_auditoria';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'accion',
        'modulo',
        'descripcion',
        'tabla_afectada',
        'id_registro_afectado',
        'datos_anteriores',
        'datos_nuevos',
        'motivo',
        'direccion_ip',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'datos_anteriores' => 'array',
            'datos_nuevos' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'id_usuario',
            'id'
        );
    }
}
