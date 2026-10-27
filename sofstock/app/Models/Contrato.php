<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contrato extends Model
{
    protected $table = 'contratos';

    protected $primaryKey = 'id_contrato';

    protected $fillable = [
        'id_inmueble',
        'numero_contrato',
        'tipo_contrato',
        'contraparte',
        'tipo_documento_contraparte',
        'documento_contraparte',
        'archivos',
        'fecha_inicio',
        'fecha_fin',
        'valor',
        'estado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'valor' => 'decimal:2',
            'archivos' => 'array',
        ];
    }

    public function inmueble(): BelongsTo
    {
        return $this->belongsTo(Inmobiliario::class, 'id_inmueble', 'id_inmueble');
    }
}
