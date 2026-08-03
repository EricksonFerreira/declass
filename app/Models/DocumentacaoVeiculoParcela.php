<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentacaoVeiculoParcela extends Model
{
    use HasFactory, HasAuditColumns;

    protected $table = 'documentacao_veiculo_parcelas';

    protected $fillable = [
        'documentacao_veiculo_id',
        'numero_parcela',
        'data_vencimento',
        'data_pagamento',
        'valor',
        'status',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'data_vencimento' => 'date',
        'data_pagamento' => 'date',
        'valor' => 'float',
        'datainc' => 'datetime',
        'datatu' => 'datetime',
    ];

    public function documentacaoVeiculo()
    {
        return $this->belongsTo(DocumentacaoVeiculo::class, 'documentacao_veiculo_id');
    }
}
