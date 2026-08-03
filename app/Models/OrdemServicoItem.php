<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdemServicoItem extends Model
{
    use HasAuditColumns;

    protected $table = 'ordem_servico_itens';

    protected $fillable = [
        'ordem_servico_id',
        'veiculo_dano_id',
        'descricao_servico',
        'resultado',
        'sort_order',
        'tipo_item',
        'quantidade',
        'valor_unitario',
        'valor_total',
        'pendencia_manutencao_preventiva_id',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'quantidade' => 'decimal:2',
        'valor_unitario' => 'decimal:2',
        'valor_total' => 'decimal:2',
        'datainc' => 'datetime',
        'datatu' => 'datetime',
    ];

    public function ordemServico(): BelongsTo
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }

    public function veiculoDano(): BelongsTo
    {
        return $this->belongsTo(VeiculoDanos::class, 'veiculo_dano_id');
    }

    public function pendenciaPreventiva(): BelongsTo
    {
        return $this->belongsTo(PendenciaManutencaoPreventiva::class, 'pendencia_manutencao_preventiva_id');
    }
}
