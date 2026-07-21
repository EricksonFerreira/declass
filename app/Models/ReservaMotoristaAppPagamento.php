<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservaMotoristaAppPagamento extends Model
{
    use HasFactory;

    protected $table = 'reserva_motorista_app_pagamentos';

    protected $fillable = [
        'reserva_motorista_app_id',
        'empresa_id',
        'user_id',
        'periodos',
        'periodo_unidade',
        'valor_unitario',
        'valor_total',
        'data_inicio_prevista',
        'data_fim_prevista',
        'status',
        'metodo_pagamento',
        'pago_em',
        'referencia_pagamento',
        'observacoes',
        'metadados',
    ];

    protected $casts = [
        'periodos' => 'integer',
        'valor_unitario' => 'decimal:2',
        'valor_total' => 'decimal:2',
        'data_inicio_prevista' => 'datetime',
        'data_fim_prevista' => 'datetime',
        'pago_em' => 'datetime',
        'metadados' => 'array',
    ];

    public function reservaMotoristaApp(): BelongsTo
    {
        return $this->belongsTo(ReservaMotoristaApp::class, 'reserva_motorista_app_id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getStatusBadgeAttribute(): array
    {
        return match (strtolower((string) $this->status)) {
            'pago' => ['label' => 'Pago', 'class' => 'badge-success'],
            'pendente' => ['label' => 'Pendente', 'class' => 'badge-warning'],
            'cancelado' => ['label' => 'Cancelado', 'class' => 'badge-secondary'],
            'vencido' => ['label' => 'Vencido', 'class' => 'badge-danger'],
            default => ['label' => ucfirst((string) $this->status), 'class' => 'badge-secondary'],
        };
    }

    public function getMetodoLabelAttribute(): string
    {
        return match (strtolower((string) $this->metodo_pagamento)) {
            'pix' => 'PIX',
            'cartao_credito', 'cartao' => 'Cartão de crédito',
            'cartao_debito' => 'Cartão de débito',
            'boleto' => 'Boleto',
            'transferencia' => 'Transferência',
            'dinheiro' => 'Dinheiro',
            default => ucfirst((string) $this->metodo_pagamento),
        };
    }
}
