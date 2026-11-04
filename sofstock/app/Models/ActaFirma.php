<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActaFirma extends Model
{
    protected $fillable = [
        'acta_id',
        'rol_id',
        'rol_nombre',
        'usuario_id',
        'nombre',
        'cargo',
    ];

    public function acta(): BelongsTo
    {
        return $this->belongsTo(Acta::class);
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id', 'id_rol');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'id');
    }
}
