<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaldoMovimentacao extends Model
{
    protected $table = 'saldo_movimentacoes';

    protected $fillable = [
        'user_id',
        'empresa_id',
        'reserva_id',
        'tipo',
        'origem',
        'valor',
        'saldo_anterior',
        'saldo_atual',
        'observacoes',
    ];
}
