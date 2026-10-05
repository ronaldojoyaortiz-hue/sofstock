<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedores extends Model
{
    protected $table = 'proveedores';

    protected $primaryKey = 'id_proveedor';

    public $timestamps = false;

    protected $fillable = [
        'razon_social',
        'nit',
        'contacto_nombre',
        'correo',
        'telefono',
        'direccion',
        'activo',
        'fecha_registro',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_registro' => 'datetime',
    ];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_proveedor', 'id_proveedor');
    }

    public function categorias()
    {
        return $this->belongsToMany(Categoria::class, 'productos', 'id_proveedor', 'id_categoria', 'id_proveedor', 'id_categoria')
            ->distinct();
    }
}
