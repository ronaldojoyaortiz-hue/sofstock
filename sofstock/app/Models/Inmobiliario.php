<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inmobiliario extends Model
{
    protected $table = 'inmuebles';

    protected $primaryKey = 'id_inmueble';

    public $timestamps = true;

    protected $fillable = [
        'codigo',
        'tipo_inmueble',
        // Some existing databases still require this legacy column.
        'nombre',
        'descripcion',
        'direccion',
        'ciudad',
        'area_m2',
        'estado',
        'observaciones',
        'archivos',
    ];

    protected function casts(): array
    {
        return [
            'area_m2' => 'decimal:2',
            'archivos' => 'array',
        ];
    }

}
