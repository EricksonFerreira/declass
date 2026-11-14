<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Periodo letivo de um ano letivo: bimestre, trimestre,
 * semestre etc. Nome, ordem e datas sao configuraveis —
 * suporta desde 4 bimestres ate semestres de faculdade.
 */
class PeriodoLetivo extends Model
{
    use HasAuditColumns, HasFactory, SoftDeletes;

    protected $table = 'periodos_letivos';

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'ano_letivo_id',
        'nome',
        'ordem',
        'data_inicio',
        'data_fim',
        'ativo',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'ordem' => 'integer',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class, 'escola_id');
    }

    public function anoLetivo(): BelongsTo
    {
        return $this->belongsTo(AnoLetivo::class, 'ano_letivo_id');
    }

    public function estaAtivo(): bool
    {
        return $this->ativo === 'S';
    }

    /** Atalho de leitura em Blade: $model->esta_ativo */
    public function getEstaAtivoAttribute()
    {
        return $this->estaAtivo();
    }
}
