<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Disciplina: componente curricular do catalogo do tenant.
 * A oferta concreta por turma fica em turma_disciplina.
 */
class Disciplina extends Model
{
    use HasAuditColumns, HasFactory, SoftDeletes;

    /** Media de aprovacao usada quando a oferta nao define uma propria. */
    public const MEDIA_APROVACAO_PADRAO = 7.0;

    protected $fillable = [
        'empresa_id',
        'nome',
        'sigla',
        'descricao',
        'ativo',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function turmas(): BelongsToMany
    {
        return $this->belongsToMany(Turma::class, 'turma_disciplina')
            ->withPivot('carga_horaria_semanal', 'media_aprovacao')
            ->withTimestamps();
    }

    /**
     * Media de aprovacao da oferta em uma turma (pivot
     * turma_disciplina). Sem valor configurado, usa o padrao.
     */
    public function mediaAprovacaoNaTurma(): float
    {
        return (float) ($this->pivot?->media_aprovacao ?? self::MEDIA_APROVACAO_PADRAO);
    }

    public function estaAtiva(): bool
    {
        return strtoupper((string) $this->ativo) === 'S';
    }
}
