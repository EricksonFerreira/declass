<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calcao extends Model
{
    use HasFactory;

    protected $table = 'calcoes';

    protected $fillable = [
        'empresa_id',
        'reserva_id',
        'contrato_checkin_id',
        'cliente_id',
        'payment_intent_id',
        'payment_method_id',
        'stripe_customer_id',
        'valor_calculado',
        'valor_autorizado',
        'valor_capturado',
        'tipo_regra_aplicada',
        'origem_regra_aplicada',
        'configuracao_calcao_id',
        'status',
        'data_hora_autorizacao',
        'data_hora_captura',
        'data_hora_cancelamento',
        'usuario_responsavel_id',
        'observacao',
        'justificativa_captura',
        'stripe_response',
    ];

    protected $casts = [
        'valor_calculado'          => 'decimal:2',
        'valor_autorizado'         => 'decimal:2',
        'valor_capturado'          => 'decimal:2',
        'data_hora_autorizacao'    => 'datetime',
        'data_hora_captura'        => 'datetime',
        'data_hora_cancelamento'   => 'datetime',
        'stripe_response'          => 'array',
    ];

    const STATUS_PENDENTE              = 'pendente';
    const STATUS_AUTORIZADO            = 'autorizado';
    const STATUS_PARCIALMENTE_CAPTURADO = 'parcialmente_capturado';
    const STATUS_CAPTURADO             = 'capturado';
    const STATUS_CANCELADO             = 'cancelado';
    const STATUS_EXPIRADO              = 'expirado';
    const STATUS_FALHOU                = 'falhou';

    const STATUS_LABELS = [
        'pendente'               => 'Pendente',
        'autorizado'             => 'Autorizado',
        'parcialmente_capturado' => 'Parcialmente Capturado',
        'capturado'              => 'Capturado',
        'cancelado'              => 'Cancelado',
        'expirado'               => 'Expirado',
        'falhou'                 => 'Falhou',
    ];

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    public function contratoCheckin()
    {
        return $this->belongsTo(ContratoCheckin::class, 'contrato_checkin_id');
    }

    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    public function usuarioResponsavel()
    {
        return $this->belongsTo(User::class, 'usuario_responsavel_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function configuracao()
    {
        return $this->belongsTo(ConfiguracaoCalcao::class, 'configuracao_calcao_id');
    }

    public function getLabelStatusAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function podeLiberar(): bool
    {
        return $this->status === self::STATUS_AUTORIZADO;
    }

    public function podeCapturar(): bool
    {
        return in_array($this->status, [self::STATUS_AUTORIZADO, self::STATUS_PARCIALMENTE_CAPTURADO], true);
    }
}
