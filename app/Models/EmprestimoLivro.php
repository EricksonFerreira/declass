<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Emprestimo de livro do acervo para um aluno.
 * status 'ativo'/'devolvido'; 'atrasado' e derivado da
 * data_devolucao_prevista (mesmo padrao de mensalidade vencida).
 */
class EmprestimoLivro extends Model
{
    use HasAuditColumns, HasFactory, SoftDeletes;

    protected $table = 'emprestimos_livros';

    protected $fillable = [
        'empresa_id',
        'livro_id',
        'aluno_id',
        'data_emprestimo',
        'data_devolucao_prevista',
        'data_devolucao',
        'status',
        'observacoes',
    ];

    protected $casts = [
        'data_emprestimo' => 'date',
        'data_devolucao_prevista' => 'date',
        'data_devolucao' => 'date',
    ];

    public const STATUS = [
        'ativo' => 'Ativo',
        'devolvido' => 'Devolvido',
    ];

    /** Opcoes do filtro de situacao (atraso e derivado, nao coluna). */
    public const SITUACOES = [
        'ativo' => 'No prazo',
        'atrasado' => 'Atrasado',
        'devolvido' => 'Devolvido',
    ];

    public function livro(): BelongsTo
    {
        return $this->belongsTo(Livro::class, 'livro_id');
    }

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class, 'aluno_id');
    }

    public function estaAtivo(): bool
    {
        return $this->status === 'ativo';
    }

    public function estaDevolvido(): bool
    {
        return $this->status === 'devolvido';
    }

    public function estaAtrasado(): bool
    {
        return $this->estaAtivo()
            && $this->data_devolucao_prevista !== null
            && $this->data_devolucao_prevista->lt(now()->startOfDay());
    }

    public function situacao(): string
    {
        if ($this->estaDevolvido()) {
            return 'devolvido';
        }

        return $this->estaAtrasado() ? 'atrasado' : 'ativo';
    }
}
