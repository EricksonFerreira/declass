<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentacaoVeiculo extends Model
{
    use HasFactory;

    protected $table = 'documentacoes_veiculo';

    protected $fillable = [
        'empresa_id',
        'veiculo_id',
        'tipo',
        'nome',
        'descricao',
        'parcelas_total',
        'valor_parcela',
        'status',
        'categoria_financeira_id',
    ];

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    public function categoriaFinanceira()
    {
        return $this->belongsTo(CategoriaFinanceira::class, 'categoria_financeira_id');
    }

    public function parcelas()
    {
        return $this->hasMany(DocumentacaoVeiculoParcela::class, 'documentacao_veiculo_id');
    }

    public function lancamentos()
    {
        return $this->hasMany(LancamentoFinanceiro::class, 'documentacao_veiculo_id');
    }
}
