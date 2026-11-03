<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionSistema extends Model
{
    protected $table = 'configuraciones_sistema';

    protected $fillable = ['nombre_empresa', 'moneda', 'idioma', 'tamano_letra'];
}
