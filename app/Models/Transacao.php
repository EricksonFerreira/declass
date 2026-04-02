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
}
