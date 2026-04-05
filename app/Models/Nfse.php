<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nfse extends Model
{
    use HasFactory;

    protected $table = 'nfse';

    protected $fillable = [
        'empresa_id',
        'reserva_id',
        'status',
        'referencia',
        'ambiente',
        'nfse_id',
        'dps_id',
        'numero',
        'serie',
        'payload',
        'response',
        'cancelamento_response',
        'cancelada_em',
        'ultima_consulta_em',
    ];

    protected $casts = [
        'payload' => 'array',
        'response' => 'array',
        'cancelamento_response' => 'array',
        'cancelada_em' => 'datetime',
        'ultima_consulta_em' => 'datetime',
    ];

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
