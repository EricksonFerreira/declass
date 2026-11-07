<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Horario nomeado: uma grade de horarios (matriz de slots) de uma
 * escola num turno. Um turno pode ter varios horarios; a turma
 * se vincula a um especifico.
 */
class Horario extends Model
{
    use HasAuditColumns, SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'turno',
        'nome',
        'ativo',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class, 'escola_id');
    }

    public function slots(): HasMany
    {
        return $this->hasMany(HorarioSlot::class, 'horario_id')
            ->orderBy('dia_semana')->orderBy('ordem');
    }

    public function turmas(): HasMany
    {
        return $this->hasMany(Turma::class, 'horario_id');
    }

    /** Dias da semana que possuem ao menos um slot (1=seg ... 7=dom). */
    public function diasLetivos(): array
    {
        return $this->slots->pluck('dia_semana')->unique()->sort()->values()->all();
    }
}
