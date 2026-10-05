<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'id_categoria',
        'id_proveedor',
        'stock_actual',
        'stock_minimo',
        'unidad_base',
        'precio_base',
        'activo',
        'fecha_registro',
        'fecha_actualizacion'
    ];

    protected $casts = [
        'stock_actual' => 'integer',
        'stock_minimo' => 'integer',
        'precio_base' => 'decimal:2',
        'activo' => 'boolean',
        'fecha_registro' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedores::class, 'id_proveedor', 'id_proveedor');
    }

    public function detallesFactura()
    {
        return $this->hasMany(DetalleFactura::class, 'id_producto', 'id');
    }
}
