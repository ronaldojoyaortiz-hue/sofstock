<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EgresoMensual extends Model
{
    protected $table = 'egresos_mensuales';

    protected $fillable = ['fecha', 'tipo', 'descripcion', 'monto', 'id_producto', 'id_proveedor'];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id');
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedores::class, 'id_proveedor', 'id_proveedor');
    }
}
