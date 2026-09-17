<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmpresaPlanoPagamento extends Model
{
    use HasAuditColumns, HasFactory;

    protected $table = 'empresa_plano_pagamentos';

    protected $fillable = [
        'empresa_plano_id',
        'empresa_id',
        'valor',
        'periodicidade',
        'metodo',
        'status',
        'data_vencimento',
        'pago_em',
        'gateway_payment_id',
        'stripe_payment_intent_id',
        'pix_qr_code_image',
        'pix_qr_code_text',
        'pix_expira_em',
        'boleto_url',
        'boleto_codigo',
        'pix_chave',
        'nosso_numero',
        'metadados',
        'referencia_interna',
        'instrucoes_pagamento',
        'registrado_por_user_id',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_vencimento' => 'date',
        'pago_em' => 'datetime',
        'pix_expira_em' => 'datetime',
        'metadados' => 'array',
        'datainc' => 'datetime',
        'datatu' => 'datetime',
    ];

    public function empresaPlano(): BelongsTo
    {
        return $this->belongsTo(EmpresaPlano::class, 'empresa_plano_id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por_user_id');
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'pago' => ['label' => 'Pago',     'class' => 'badge-success'],
            'pendente' => ['label' => 'Pendente', 'class' => 'badge-warning'],
            'vencido' => ['label' => 'Vencido',  'class' => 'badge-danger'],
            'cancelado' => ['label' => 'Cancelado', 'class' => 'badge-secondary'],
            default => ['label' => ucfirst($this->status), 'class' => 'badge-secondary'],
        };
    }

    public function getMetodoTextoAttribute(): string
    {
        return match ($this->metodo) {
            'boleto' => 'Boleto Bancário',
            'cartao' => 'Cartão de Crédito',
            'pix' => 'PIX',
            'dinheiro' => 'Dinheiro em Espécie',
            'transferencia' => 'Transferência Bancária',
            'manual' => 'Manual',
            default => ucfirst($this->metodo),
        };
    }

    public function getPlanoAttribute()
    {
        return $this->empresaPlano?->plano;
    }

    public function getPeriodicidadeTextoAttribute(): string
    {
        return $this->periodicidade === 'anual' ? 'Anual' : 'Mensal';
    }

    public function getMetodoCobrancaTextoAttribute(): string
    {
        return $this->metodo_texto;
    }
}
