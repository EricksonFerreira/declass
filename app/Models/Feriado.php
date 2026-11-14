<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Feriado/dia sem aula da escola: cobre um periodo e informa se
 * suspende as aulas (S) ou convive com aula normal (N).
 * escola_id nulo = toda a rede.
 */
class Feriado extends Model
{
    use HasAuditColumns, SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'nome',
        'data_inicio',
        'data_fim',
        'suspende_aulas',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class, 'escola_id');
    }

    /** Fim efetivo do periodo (inicio quando nao informado). */
    public function periodoFim()
    {
        return $this->data_fim ?? $this->data_inicio;
    }

    /** Atalho de leitura em Blade: $model->periodo_fim */
    public function getPeriodoFimAttribute()
    {
        return $this->periodoFim();
    }
}
