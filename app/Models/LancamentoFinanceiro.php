<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LancamentoFinanceiro extends Model
{
    use HasFactory;

    protected $table = 'lancamentos_financeiros';

    protected $fillable = [
        'empresa_id',
        'descricao',
        'valor',
        'tipo',
        'categoria_financeira_id',
        'data_vencimento',
        'data_pagamento',
        'status',
        'metodo_pagamento',
        'veiculo_id',
        'reserva_id',
        'user_id',
        'observacoes',
    ];

    protected $casts = [
        'valor' => 'float',
        'data_vencimento' => 'date:Y-m-d',
        'data_pagamento' => 'date:Y-m-d',
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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
