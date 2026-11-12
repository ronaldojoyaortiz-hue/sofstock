<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReporteGenerado extends Model
{
    protected $table = 'reportes_generados';

    protected $fillable = [
        'tipo_reporte',
        'fecha_desde',
        'fecha_hasta',
        'datos',
        'id_usuario',
    ];

    protected $casts = [
        'fecha_desde' => 'date',
        'fecha_hasta' => 'date',
        'datos' => 'array',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id');
    }
}
