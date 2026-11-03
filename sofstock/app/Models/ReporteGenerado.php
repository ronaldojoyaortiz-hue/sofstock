<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
