<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nfse extends Model
{
    use HasAuditColumns, HasFactory;

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
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'payload' => 'array',
        'response' => 'array',
        'cancelamento_response' => 'array',
        'cancelada_em' => 'datetime',
        'ultima_consulta_em' => 'datetime',
        'datainc' => 'datetime',
        'datatu' => 'datetime',
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
