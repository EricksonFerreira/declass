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
        'codigo',
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

    /**
     * Gera o protocolo amigavel (3 letras + 3 digitos embaralhados,
     * unico por tenant) quando nao informado — cobre tanto a fila
     * interna quanto o formulario publico.
     */
    protected static function booted(): void
    {
        static::creating(function (SolicitacaoMatricula $solicitacao): void {
            if (! $solicitacao->codigo) {
                $solicitacao->codigo = static::gerarCodigo((int) $solicitacao->empresa_id);
            }
        });
    }

    /**
     * Protocolo de 6 caracteres unico dentro do tenant:
     * 3 letras + 3 digitos com posicoes variadas (ex.: K4M9A2, 7B2KQM).
     * Alfabeto sem I/O e digitos sem 0/1 para nao confundir leitura.
     */
    public static function gerarCodigo(int $empresaId): string
    {
        do {
            $chars = array_merge(
                self::sortear('ABCDEFGHJKLMNPQRSTUVWXYZ', 3),
                self::sortear('23456789', 3),
            );
            shuffle($chars);
            $codigo = implode('', $chars);
        } while (static::query()->withTrashed()->where('empresa_id', $empresaId)->where('codigo', $codigo)->exists());

        return $codigo;
    }

    /** @return string[] */
    private static function sortear(string $alfabeto, int $qtd): array
    {
        $max = strlen($alfabeto) - 1;
        $out = [];
        for ($i = 0; $i < $qtd; $i++) {
            $out[] = $alfabeto[random_int(0, $max)];
        }

        return $out;
    }

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

    /** Datas prontas para exibicao em Blade (sem ->format() na view). */
    public function getRecebidaEmAttribute(): ?string
    {
        return $this->created_at?->format('d/m/Y H:i');
    }

    public function getAnalisadaEmAttribute(): ?string
    {
        return $this->data_analise?->format('d/m/Y H:i');
    }

    public function getNascimentoAlunoBrAttribute(): ?string
    {
        return $this->data_nascimento_aluno?->format('d/m/Y');
    }

    /** Valor para input type=date (Y-m-d) no formulario. */
    public function getNascimentoAlunoIsoAttribute(): ?string
    {
        return $this->data_nascimento_aluno?->format('Y-m-d');
    }

    /** Atalho de leitura em Blade: $model->esta_pendente */
    public function getEstaPendenteAttribute()
    {
        return $this->estaPendente();
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
}
