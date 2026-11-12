<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Producto extends Model
{
    protected $table = 'productos';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'marca',
        'id_categoria',
        'id_proveedor',
        'stock_actual',
        'stock_minimo',
        'unidad_base',
        'precio_base',
        'activo',
        'fecha_caducidad',
        'fecha_registro',
        'fecha_actualizacion',
    ];

    protected $casts = [
        'stock_actual' => 'integer',
        'stock_minimo' => 'integer',
        'precio_base' => 'decimal:2',
        'activo' => 'boolean',
        'fecha_caducidad' => 'date',
        'fecha_registro' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    public function getEstadoAttribute(): string
    {
        return (float) ($this->attributes['stock_actual'] ?? 0) > 0
            ? 'Disponible'
            : 'Agotado';
    }

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

    public function usuariosDestinatariosAlertaStock(): BelongsToMany
    {
        return $this->belongsToMany(
            Usuario::class,
            'producto_alerta_stock_usuario',
            'id_producto',
            'id_usuario',
            'id',
            'id'
        )->withTimestamps();
    }
}
