<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Parada de embarque de uma rota de transporte.
 */
class ParadaTransporte extends Model
{
    use HasAuditColumns, HasFactory, SoftDeletes;

    protected $table = 'paradas_transporte';

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'rota_id',
        'nome',
        'ordem',
        'horario_previsto',
    ];

    protected $casts = [
        'ordem' => 'integer',
        'horario_previsto' => 'datetime:H:i',
    ];

    public function rota(): BelongsTo
    {
        return $this->belongsTo(RotaTransporte::class, 'rota_id');
    }

    public function passageiros(): HasMany
    {
        return $this->hasMany(TransporteAluno::class, 'parada_id');
    }
}
