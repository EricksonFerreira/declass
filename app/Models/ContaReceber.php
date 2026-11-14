<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Conta a receber: receita nao-tuition da escola/rede (taxas,
 * material, uniforme, transporte, eventos etc.).
 * Ciclo: prevista -> recebida (com data_recebimento) ou cancelada.
 * aluno_id nulo = cobranca generica (nao vinculada a um aluno).
 */
class ContaReceber extends Model
{
    use HasAuditColumns, SoftDeletes;

    protected $table = 'contas_receber';

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'aluno_id',
        'descricao',
        'categoria',
        'valor',
        'data_vencimento',
        'data_recebimento',
        'status',
        'forma_recebimento',
        'observacoes',
    ];

    protected $casts = [
        'valor' => 'float',
        'data_vencimento' => 'date',
        'data_recebimento' => 'date',
    ];

    public const STATUS = [
        'prevista' => 'Prevista',
        'recebida' => 'Recebida',
        'cancelada' => 'Cancelada',
    ];

    public const CATEGORIAS = [
        'taxas' => 'Taxas e encargos',
        'material' => 'Material didático / uniforme',
        'transporte' => 'Transporte escolar',
        'alimentacao' => 'Alimentação / cantina',
        'eventos' => 'Eventos e atividades',
        'servicos' => 'Serviços prestados',
        'doacoes' => 'Doações / contribuições',
        'outros' => 'Outros',
    ];

    public const FORMAS_RECEBIMENTO = [
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

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class, 'aluno_id');
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

    /** Conta prevista com vencimento passado = em atraso. */
    public function getSituacaoAttribute(): string
    {
        if ($this->status === 'prevista' && $this->data_vencimento?->isPast()) {
            return 'atrasada';
        }

        return $this->status;
    }
}
