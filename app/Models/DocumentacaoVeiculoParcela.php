<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentacaoVeiculoParcela extends Model
{
    use HasFactory;

    protected $table = 'documentacao_veiculo_parcelas';

    protected $fillable = [
        'documentacao_veiculo_id',
        'numero_parcela',
        'data_vencimento',
        'data_pagamento',
        'valor',
        'status',
    ];

    protected $casts = [
        'data_vencimento' => 'date',
        'data_pagamento' => 'date',
        'valor' => 'float',
    ];

    public function documentacaoVeiculo()
    {
        return $this->belongsTo(DocumentacaoVeiculo::class, 'documentacao_veiculo_id');
    }
}
