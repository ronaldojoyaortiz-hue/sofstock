<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MermaProducto extends Model
{
    protected $table = 'mermas_productos';

    protected $fillable = ['id_producto', 'fecha', 'cantidad', 'valor_perdida', 'motivo'];

    protected $casts = [
        'fecha' => 'date',
        'cantidad' => 'decimal:2',
        'valor_perdida' => 'decimal:2',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id');
    }
}
