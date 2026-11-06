<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Solicitacao de matricula: pedido de vaga registrado pela
 * secretaria (ou futuramente pelo portal publico). A aprovacao
 * gera Pessoa+Aluno+Matricula+Responsavel em transacao.
 */
class SolicitacaoMatricula extends Model
{
    use HasAuditColumns, HasFactory, SoftDeletes;

    protected $table = 'solicitacoes_matricula';

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'ano_letivo_id',
        'etapa_id',
        'turma_id',
        'nome_aluno',
        'data_nascimento_aluno',
        'sexo_aluno',
        'nome_responsavel',
        'parentesco',
        'telefone_responsavel',
        'email_responsavel',
        'cpf_responsavel',
        'origem',
        'status',
        'observacoes',
        'analisado_por',
        'data_analise',
        'motivo_recusa',
        'aluno_id',
        'responsavel_id',
        'matricula_id',
    ];

    protected $casts = [
        'data_nascimento_aluno' => 'date',
        'data_analise' => 'datetime',
    ];

    public const STATUS = [
        'pendente' => 'Pendente',
        'em_analise' => 'Em análise',
        'aprovada' => 'Aprovada',
        'recusada' => 'Recusada',
        'cancelada' => 'Cancelada',
    ];

    public const ORIGENS = [
        'interno' => 'Registro interno',
        'portal' => 'Portal público',
    ];

    public const PARENTESCOS = [
        'mae' => 'Mãe',
        'pai' => 'Pai',
        'responsavel_legal' => 'Responsável legal',
        'avó' => 'Avó',
        'avô' => 'Avô',
        'tio' => 'Tio(a)',
        'outro' => 'Outro',
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

    public function etapa(): BelongsTo
    {
        return $this->belongsTo(Etapa::class, 'etapa_id');
    }

    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class, 'turma_id');
    }

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class, 'aluno_id');
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(Responsavel::class, 'responsavel_id');
    }

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }

    public function analista(): BelongsTo
    {
        return $this->belongsTo(User::class, 'analisado_por');
    }

    public function estaPendente(): bool
    {
        return in_array($this->status, ['pendente', 'em_analise'], true);
    }

    public function statusLabel(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }
}
