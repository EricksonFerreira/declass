<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdemServicoItem extends Model
{
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
    ];

    public function ordemServico(): BelongsTo
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }

    public function veiculoDano(): BelongsTo
    {
        return $this->belongsTo(VeiculoDanos::class, 'veiculo_dano_id');
    }
}
