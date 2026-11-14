<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Matricula: inscricao de um aluno em uma turma/ano letivo.
 * Historico e preservado — cancelamentos/trancamentos mudam o
 * status, nao apagam o registro.
 */
class Matricula extends Model
{
    use HasAuditColumns, HasFactory, SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'aluno_id',
        'turma_id',
        'ano_letivo_id',
        'status',
        'plano_mensalidade_id',
        'bolsa_percentual',
        'numero_chamada',
        'data_matricula',
        'data_saida',
        'observacoes',
    ];

    protected $casts = [
        'numero_chamada' => 'integer',
        'bolsa_percentual' => 'float',
        'data_matricula' => 'date',
        'data_saida' => 'date',
    ];

    public const STATUS = [
        'ativa' => 'Ativa',
        'trancada' => 'Trancada',
        'cancelada' => 'Cancelada',
        'concluida' => 'Concluída',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class, 'aluno_id');
    }

    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class, 'turma_id');
    }

    public function anoLetivo(): BelongsTo
    {
        return $this->belongsTo(AnoLetivo::class, 'ano_letivo_id');
    }

    public function planoMensalidade(): BelongsTo
    {
        return $this->belongsTo(PlanoMensalidade::class, 'plano_mensalidade_id');
    }

    public function mensalidades(): HasMany
    {
        return $this->hasMany(Mensalidade::class, 'matricula_id');
    }

    public function estaAtiva(): bool
    {
        return $this->status === 'ativa';
    }

    /** Atalho de leitura em Blade: $model->esta_ativa */
    public function getEstaAtivaAttribute()
    {
        return $this->estaAtiva();
    }
}
