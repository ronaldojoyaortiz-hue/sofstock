<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CambioPresupuesto extends Model
{
    protected $table = 'historial_presupuestos';

    protected $fillable = [
        'mes',
        'presupuesto_anterior',
        'presupuesto_nuevo',
        'id_usuario',
    ];

    protected $casts = [
        'mes' => 'date',
        'presupuesto_anterior' => 'decimal:2',
        'presupuesto_nuevo' => 'decimal:2',
    ];
}
