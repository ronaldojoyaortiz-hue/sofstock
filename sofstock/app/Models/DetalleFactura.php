<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleFactura extends Model
{
    protected $table = 'detalle_facturas';

    protected $primaryKey = 'id_detalle_factura';

    protected $fillable = [
        'id_producto',
        'producto_nombre',
        'cantidad',
        'precio_unitario',
        'porcentaje_impuesto',
        'descuento',
        'subtotal',
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'porcentaje_impuesto' => 'decimal:2',
        'descuento' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function factura()
    {
        return $this->belongsTo(Facturas::class, 'id_factura', 'id_factura');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id');
    }
}
