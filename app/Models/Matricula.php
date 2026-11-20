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
        'bolsa_tipo',
        'bolsa_valor',
        'numero_chamada',
        'data_matricula',
        'data_saida',
        'observacoes',
    ];

    protected $casts = [
        'numero_chamada' => 'integer',
        'bolsa_percentual' => 'float',
        'bolsa_valor' => 'float',
        'data_matricula' => 'date',
        'data_saida' => 'date',
    ];

    /** Modalidades de bolsa/desconto na mensalidade. */
    public const BOLSA_TIPOS = [
        'percentual' => 'Percentual (%)',
        'valor' => 'Valor fixo (R$)',
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

    /** Atalho de leitura em Blade: $model->status_label */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    /**
     * Valor da mensalidade com a bolsa aplicada sobre o plano.
     * Percentual abate proporcionalmente; valor fixo abate R$ por parcela.
     */
    public function valorComBolsa(): ?float
    {
        $valorPlano = (float) ($this->planoMensalidade?->valor ?? 0);

        if ($valorPlano <= 0) {
            return null;
        }

        if ($this->bolsa_tipo === 'valor' && (float) $this->bolsa_valor > 0) {
            return max(0, round($valorPlano - $this->bolsa_valor, 2));
        }

        $percentual = min((float) ($this->bolsa_percentual ?? 0), 100);

        return $percentual > 0 ? round($valorPlano * (1 - $percentual / 100), 2) : $valorPlano;
    }

    /** Atalho de leitura em Blade: $model->bolsa_label ("20%" / "R$ 150,00" / null). */
    public function getBolsaLabelAttribute(): ?string
    {
        if ($this->bolsa_tipo === 'valor' && (float) $this->bolsa_valor > 0) {
            return 'R$ '.number_format($this->bolsa_valor, 2, ',', '.');
        }

        if ((float) ($this->bolsa_percentual ?? 0) > 0) {
            return rtrim(rtrim(number_format($this->bolsa_percentual, 2, ',', ''), '0'), ',').'%';
        }

        return null;
    }
}
