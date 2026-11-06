<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mensalidade extends Model
{
    use HasAuditColumns, SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'matricula_id',
        'plano_mensalidade_id',
        'competencia',
        'valor',
        'vencimento',
        'status',
        'data_pagamento',
        'forma_pagamento',
        'observacoes',
    ];

    protected $casts = [
        'valor' => 'float',
        'vencimento' => 'date',
        'data_pagamento' => 'date',
    ];

    public const STATUS = [
        'pendente' => 'Pendente',
        'paga' => 'Paga',
        'cancelada' => 'Cancelada',
    ];

    public const FORMAS_PAGAMENTO = [
        'dinheiro' => 'Dinheiro',
        'pix' => 'Pix',
        'cartao_credito' => 'Cartão de crédito',
        'cartao_debito' => 'Cartão de débito',
        'boleto' => 'Boleto',
        'transferencia' => 'Transferência',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }

    public function planoMensalidade(): BelongsTo
    {
        return $this->belongsTo(PlanoMensalidade::class, 'plano_mensalidade_id');
    }

    /** Situacao derivada: pendente com vencimento passado = vencida. */
    public function getSituacaoAttribute(): string
    {
        if ($this->status === 'pendente' && $this->vencimento?->isPast()) {
            return 'vencida';
        }

        return $this->status;
    }
}
