<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Notificacion extends Model
{
    public const TIPO_MANUAL = 'manual';

    public const TIPO_STOCK_BAJO = 'stock_bajo';

    protected $table = 'notificaciones';

    protected $fillable = [
        'usuario_emisor_id',
        'id_producto',
        'titulo',
        'mensaje',
        'tipo',
        'fecha_evento',
        'lugar',
        'datos',
    ];

    protected $casts = [
        'fecha_evento' => 'datetime',
        'datos' => 'array',
    ];

    public function emisor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_emisor_id', 'id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id');
    }

    public function usuariosDestinatarios(): BelongsToMany
    {
        return $this->belongsToMany(
            Usuario::class,
            'notificacion_usuario',
            'id_notificacion',
            'id_usuario',
            'id',
            'id'
        )->withPivot('leida_at')->withTimestamps();
    }
}
