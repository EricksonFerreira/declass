<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Despesa: conta a pagar da escola ou da rede (escola_id nulo).
 * Ciclo: prevista -> paga (com data_pagamento) ou cancelada.
 */
class Despesa extends Model
{
    use HasAuditColumns, SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'fornecedor',
        'descricao',
        'categoria',
        'valor',
        'data_vencimento',
        'data_pagamento',
        'status',
        'forma_pagamento',
        'observacoes',
    ];

    protected $casts = [
        'valor' => 'float',
        'data_vencimento' => 'date',
        'data_pagamento' => 'date',
    ];

    public const STATUS = [
        'prevista' => 'Prevista',
        'paga' => 'Paga',
        'cancelada' => 'Cancelada',
    ];

    public const CATEGORIAS = [
        'pessoal' => 'Pessoal / salários',
        'material' => 'Material / consumo',
        'servicos' => 'Serviços / terceiros',
        'utilidades' => 'Água, luz e internet',
        'manutencao' => 'Manutenção / obras',
        'transporte' => 'Transporte escolar',
        'alimentacao' => 'Alimentação / cantina',
        'impostos' => 'Impostos / taxas',
        'equipamentos' => 'Equipamentos',
        'outros' => 'Outros',
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

    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class, 'escola_id');
    }

    public function categoriaLabel(): string
    {
        return self::CATEGORIAS[$this->categoria] ?? $this->categoria;
    }

    /** Atalho de leitura em Blade: $model->categoria_label */
    public function getCategoriaLabelAttribute()
    {
        return $this->categoriaLabel();
    }

    /** Despesa prevista com vencimento passado = em atraso. */
    public function getSituacaoAttribute(): string
    {
        if ($this->status === 'prevista' && $this->data_vencimento?->isPast()) {
            return 'atrasada';
        }

        return $this->status;
    }
}
