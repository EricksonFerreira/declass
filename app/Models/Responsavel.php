<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Responsavel: responsavel legal, pedagogico ou financeiro de
 * alunos. Dados pessoais em `pessoas`; vinculos N:N com papeis
 * e autorizacoes ficam em `aluno_responsavel`.
 */
class Responsavel extends Model
{
    use HasAuditColumns, HasFactory, SoftDeletes;

    protected $table = 'responsaveis';

    protected $fillable = [
        'empresa_id',
        'pessoa_id',
        'user_id',
        'profissao',
        'local_trabalho',
        'observacoes',
        'ativo',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function pessoa(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function alunos(): BelongsToMany
    {
        return $this->belongsToMany(Aluno::class, 'aluno_responsavel', 'responsavel_id', 'aluno_id')
            ->withPivot(['id', 'tipo', 'principal', 'pode_retirar', 'autoriza_contato', 'restricoes', 'observacoes'])
            ->withTimestamps();
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
