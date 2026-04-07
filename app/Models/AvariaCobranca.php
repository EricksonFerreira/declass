<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvariaCobranca extends Model
{
    use HasFactory;

    protected $table = 'avaria_cobrancas';

    protected $fillable = [
        'empresa_id',
        'reserva_id',
        'veiculo_id',
        'cliente_id',
        'calcao_id',
        'metodo',
        'status',
        'moeda',
        'valor',
        'descricao',
        'dano_veiculo_ids',
        'gateway_payment_intent_id',
        'gateway_response',
        'usuario_responsavel_id',
        'cobrado_em',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'dano_veiculo_ids' => 'array',
        'gateway_response' => 'array',
        'cobrado_em' => 'datetime',
    ];

    public const STATUS_PENDENTE = 'pendente';
    public const STATUS_PROCESSANDO = 'processando';
    public const STATUS_COBRADO = 'cobrado';
    public const STATUS_FALHOU = 'falhou';
    public const STATUS_CANCELADO = 'cancelado';

    public const METODO_CALCAO = 'calcao_capture';
    public const METODO_STRIPE = 'stripe_charge';
    public const METODO_MANUAL = 'manual';

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    public function calcao()
    {
        return $this->belongsTo(Calcao::class, 'calcao_id');
    }

    public function usuarioResponsavel()
    {
        return $this->belongsTo(User::class, 'usuario_responsavel_id');
    }
}

