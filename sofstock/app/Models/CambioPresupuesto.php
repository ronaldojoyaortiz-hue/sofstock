<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id');
    }
}
