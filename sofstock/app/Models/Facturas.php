<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facturas extends Model
{
    protected $table = 'facturas';

    protected $primaryKey = 'id_factura';

    protected $fillable = [
        'numero',
        'cliente_nombre',
        'cliente_documento',
        'fecha',
        'descuento',
        'subtotal',
        'impuesto',
        'total',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
        'subtotal' => 'decimal:2',
        'impuesto' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function detalles()
    {
        return $this->hasMany(DetalleFactura::class, 'id_factura', 'id_factura');
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'detalle_facturas', 'id_factura', 'id_producto', 'id_factura', 'id')
            ->withPivot('id_detalle_factura', 'producto_nombre', 'cantidad', 'precio_unitario', 'porcentaje_impuesto', 'descuento', 'subtotal');
    }
}
