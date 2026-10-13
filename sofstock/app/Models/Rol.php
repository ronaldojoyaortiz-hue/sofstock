<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Rol extends Model
{
    protected $table = 'roles';

    protected $primaryKey = 'id_rol';

    public $timestamps = false;

    protected $fillable = [
        'nombre_rol',
        'descripcion',
    ];

    public function permisos(): BelongsToMany
    {
        return $this->belongsToMany(
            Permiso::class,
            'permiso_rol',
            'id_rol',
            'id_permiso'
        )->withTimestamps();
    }
}
