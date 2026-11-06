<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Aluno: vinculo academico de uma pessoa com a escola/unidade.
 * Historico de matriculas, frequencia e notas serao entidades
 * proprias nas proximas fases — aqui ficam os dados cadastrais
 * do aluno e os dados de saude/acessibilidade.
 */
class Aluno extends Model
{
    use HasAuditColumns, HasFactory, SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'pessoa_id',
        'escola_id',
        'user_id',
        'registro_academico',
        'data_ingresso',
        'turno_preferencial',
        'necessidades_especiais',
        'informacoes_saude',
        'contatos_emergencia',
        'restricoes_acesso',
        'observacoes',
        'ativo',
    ];

    protected $casts = [
        'data_ingresso' => 'date',
    ];

    public const TURNOS = [
        'matutino' => 'Matutino',
        'vespertino' => 'Vespertino',
        'integral' => 'Integral',
    ];

    /**
     * Papeis possiveis do responsavel no vinculo com o aluno.
     */
    public const TIPOS_RESPONSAVEL = [
        'legal' => 'Responsável legal',
        'pedagogico' => 'Responsável pedagógico',
        'financeiro' => 'Responsável financeiro',
        'outro' => 'Outro',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }

    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class, 'escola_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function responsaveis(): BelongsToMany
    {
        return $this->belongsToMany(Responsavel::class, 'aluno_responsavel', 'aluno_id', 'responsavel_id')
            ->withPivot(['id', 'tipo', 'principal', 'pode_retirar', 'autoriza_contato', 'restricoes', 'observacoes'])
            ->withTimestamps();
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'aluno_id');
    }

    public function estaAtivo(): bool
    {
        return strtoupper((string) $this->ativo) === 'S';
    }

    public function getNomeAttribute(): ?string
    {
        return $this->pessoa?->nomeExibicao();
    }
}
