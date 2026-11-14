<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Pedido feito pela familia/aluno a secretaria da escola:
 * declaracao de matricula, 2a via de boleto, atendimento etc.
 */
class Solicitacao extends Model
{
    use HasAuditColumns, SoftDeletes;

    protected $table = 'solicitacoes';

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'aluno_id',
        'user_id',
        'tipo',
        'tipo_solicitacao_id',
        'titulo',
        'descricao',
        'dados',
        'status',
        'resposta',
        'respondida_por',
        'respondida_em',
    ];

    protected $casts = [
        'respondida_em' => 'datetime',
        'dados' => 'array',
    ];

    public const TIPOS = [
        'declaracao' => 'Declaração',
        'financeiro' => 'Financeiro',
        'academico' => 'Acadêmico',
        'transporte' => 'Transporte',
        'outro' => 'Outro',
    ];

    public const STATUS = [
        'aberta' => 'Aberta',
        'em_analise' => 'Em análise',
        'atendida' => 'Atendida',
        'negada' => 'Negada',
    ];

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class, 'aluno_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function respondidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'respondida_por');
    }

    /** Tipo cadastrado pela secretaria (withTrashed: historico preserva o nome). */
    public function tipoSolicitacao(): BelongsTo
    {
        return $this->belongsTo(SolicitacaoTipo::class, 'tipo_solicitacao_id')->withTrashed();
    }

    public function anexos(): HasMany
    {
        return $this->hasMany(SolicitacaoAnexo::class, 'solicitacao_id');
    }

    /** Conversa entre familia e secretaria ate o atendimento final. */
    public function mensagens(): HasMany
    {
        return $this->hasMany(SolicitacaoMensagem::class, 'solicitacao_id');
    }

    /** Familia ainda pode responder: conversa aberta enquanto nao finalizada. */
    public function conversaAberta(): bool
    {
        return in_array($this->status, ['aberta', 'em_analise'], true);
    }

    /** Atalho de leitura em Blade: $model->conversa_aberta */
    public function getConversaAbertaAttribute()
    {
        return $this->conversaAberta();
    }

    public function tipoLabel(): string
    {
        return $this->tipoSolicitacao->nome ?? (self::TIPOS[$this->tipo] ?? $this->tipo);
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
}
