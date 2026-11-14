<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Plano de atendimento especializado (PEI/PDI/PAEE) do aluno.
 */
class PlanoAtendimento extends Model
{
    use HasAuditColumns, HasFactory, SoftDeletes;

    protected $table = 'planos_atendimento';

    public const TIPOS = [
        'pei' => 'PEI — Plano Educacional Individualizado',
        'pdi' => 'PDI — Plano de Desenvolvimento Individual',
        'paee' => 'PAEE — Plano de Atendimento Educacional Especializado',
        'outro' => 'Outro plano',
    ];

    public const STATUS = [
        'elaboracao' => 'Em elaboração',
        'ativo' => 'Ativo',
        'suspenso' => 'Suspenso',
        'encerrado' => 'Encerrado',
    ];

    protected $fillable = [
        'empresa_id',
        'aluno_id',
        'escola_id',
        'user_id',
        'tipo',
        'status',
        'data_elaboracao',
        'proxima_revisao',
        'diagnostico',
        'necessidades',
        'objetivos',
        'estrategias',
        'observacoes',
        'ativo',
    ];

    protected $casts = [
        'data_elaboracao' => 'date',
        'proxima_revisao' => 'date',
    ];

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class, 'aluno_id');
    }

    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class, 'escola_id');
    }

    public function profissional(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tipoLabel(): string
    {
        return self::TIPOS[$this->tipo] ?? $this->tipo;
    }

    /** Atalho de leitura em Blade: $model->tipo_label */
    public function getTipoLabelAttribute()
    {
        return $this->tipoLabel();
    }

    public function statusLabel(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    /** Atalho de leitura em Blade: $model->status_label */
    public function getStatusLabelAttribute()
    {
        return $this->statusLabel();
    }

    public function revisaoVencida(): bool
    {
        return $this->proxima_revisao !== null
            && $this->proxima_revisao->isPast()
            && in_array($this->status, ['ativo', 'elaboracao'], true);
    }

    /** Atalho de leitura em Blade: $model->revisao_vencida */
    public function getRevisaoVencidaAttribute()
    {
        return $this->revisaoVencida();
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
