<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;

class SaldoMovimentacao extends Model
{
    use HasAuditColumns;

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
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'saldo_anterior' => 'decimal:2',
        'saldo_atual' => 'decimal:2',
        'datainc' => 'datetime',
        'datatu' => 'datetime',
    ];
}
