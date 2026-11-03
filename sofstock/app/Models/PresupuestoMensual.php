<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresupuestoMensual extends Model
{
    protected $table = 'presupuestos_mensuales';

    protected $fillable = ['mes', 'presupuesto_inicial'];

    protected $casts = [
        'mes' => 'date',
        'presupuesto_inicial' => 'decimal:2',
    ];
}
