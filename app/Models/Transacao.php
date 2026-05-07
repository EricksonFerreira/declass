<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transacao extends Model
{
    use HasFactory;

    protected $table = 'transacoes';

    protected $fillable = [
        'user_id',
        'pago_por_user_id',
        'reserva_id',
        'valor',
        'moeda',
        'metodo_pagamento',
        'gateway_transaction_id',
        'metodo_pagamento_id',
        'tipo_pagamento_id',
        'status',
        'info_pagamento',
        'confirmado_em',
        'confirmado_por_user_id',
    ];

    protected $casts = [
        'info_pagamento' => 'array',
    ];

    public function reserva()
    {
        return $this->belongsTo(Reserva::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pagoPor()
    {
        return $this->belongsTo(User::class, 'pago_por_user_id');
    }

    public function getMetodoPagamentoLabelAttribute(): string
    {
        return match (strtolower($this->metodo_pagamento ?? '')) {
            'stripe' => 'Cartão',
            'credit_card', 'card' => 'Cartão',
            'dinheiro', 'cash', 'money' => 'Dinheiro',
            'pix' => 'PIX',
            'mercado_livre', 'mercadolivre', 'mercadopago' => 'Mercado Pago',
            'paypal' => 'PayPal',
            'boleto' => 'Boleto',
            'transferencia', 'transfer' => 'Transferência',
            'debito', 'debit' => 'Débito',
            default => $this->metodo_pagamento ?? 'Não classificado',
        };
    }
}
