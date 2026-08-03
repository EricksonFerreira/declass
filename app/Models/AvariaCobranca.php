<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvariaCobranca extends Model
{
    use HasFactory, HasAuditColumns;

    protected $table = 'avaria_cobrancas';

    protected $fillable = [
        'empresa_id',
        'reserva_codigo_id',
        'veiculo_id',
        'cliente_id',
        'calcao_id',

        'metodo',
        'status',
        'moeda',

        'valor',
        'valor_calcao',
        'valor_stripe',
        'valor_pendente',

        'descricao',
        'dano_veiculo_ids',

        'gateway_payment_intent_id',
        'gateway_response',

        'usuario_responsavel_id',
        'cobrado_em',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'valor_calcao' => 'decimal:2',
        'valor_stripe' => 'decimal:2',
        'valor_pendente' => 'decimal:2',

        'dano_veiculo_ids' => 'array',
        'gateway_response' => 'array',
        'cobrado_em' => 'datetime',
        'datainc' => 'datetime',
        'datatu' => 'datetime',
    ];

    public const STATUS_PENDENTE = 'pendente';

    public const STATUS_PROCESSANDO = 'processando';

    public const STATUS_COBRADO = 'cobrado';

    public const STATUS_FALHOU = 'falhou';

    public const STATUS_CANCELADO = 'cancelado';

    public const METODO_MISTO = 'misto';

    public const METODO_CALCAO = 'calcao_capture';

    public const METODO_STRIPE = 'stripe_charge';

    public const METODO_MANUAL = 'manual';

    public function reservaCodigo(): BelongsTo
    {
        return $this->belongsTo(
            ReservaCodigo::class,
            'reserva_codigo_id'
        );
    }

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(
            Veiculo::class,
            'veiculo_id'
        );
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'cliente_id'
        );
    }

    public function calcao(): BelongsTo
    {
        return $this->belongsTo(
            Calcao::class,
            'calcao_id'
        );
    }

    public function usuarioResponsavel(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'usuario_responsavel_id'
        );
    }
}
