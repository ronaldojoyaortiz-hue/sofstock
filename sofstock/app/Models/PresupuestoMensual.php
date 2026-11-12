<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PresupuestoMensual extends Model
{
    protected $table = 'presupuestos_mensuales';

    protected $fillable = ['mes', 'presupuesto_inicial'];

    protected $casts = [
        'mes' => 'date',
        'presupuesto_inicial' => 'decimal:2',
    ];

    public function cambios(): HasMany
    {
        return $this->hasMany(CambioPresupuesto::class, 'mes', 'mes');
    }
}
