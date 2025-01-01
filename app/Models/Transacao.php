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
        'reserva_id',
        'valor',
        'metodo_pagamento',
        'metodo_pagamento_id', // traduzir para o nome do metodo de pagamento
        'tipo_pagamento_id',
        'status',
    ];

    public function reserva()
    {
        return $this->belongsTo(Reserva::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
