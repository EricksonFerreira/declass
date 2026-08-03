<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LancamentoFinanceiro extends Model
{
    use HasFactory, HasAuditColumns;

    protected $table = 'lancamentos_financeiros';

    protected $fillable = [
        'empresa_id',
        'descricao',
        'valor',
        'tipo',
        'categoria_financeira_id',
        'data_lancamento',
        'data_vencimento',
        'data_pagamento',
        'status',
        'metodo_pagamento',
        'veiculo_id',
        'documentacao_veiculo_id',
        'reserva_id',
        'user_id',
        'observacoes',
        'recorrente',
        'recorrencia',
        'parcelas_totais',
        'parcela_atual',
        'grupo_recorrencia_id',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'valor' => 'float',
        'data_lancamento' => 'datetime',
        'data_vencimento' => 'date:Y-m-d',
        'data_pagamento' => 'date:Y-m-d',
        'datainc' => 'datetime',
        'datatu' => 'datetime',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaFinanceira::class, 'categoria_financeira_id');
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    public function documentacaoVeiculo()
    {
        return $this->belongsTo(DocumentacaoVeiculo::class, 'documentacao_veiculo_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
