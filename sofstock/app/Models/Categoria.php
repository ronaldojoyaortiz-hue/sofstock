<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = 'categorias';

    protected $primaryKey = 'id_categoria';

    public $timestamps = false;

    protected $fillable = ['nombre', 'descripcion', 'activa', 'fecha_registro'];

    protected $casts = [
        'activa' => 'boolean',
    ];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_categoria', 'id_categoria');
    }

    public function proveedores()
    {
        return $this->belongsToMany(Proveedores::class, 'productos', 'id_categoria', 'id_proveedor', 'id_categoria', 'id_proveedor')
            ->distinct();
    }
}
