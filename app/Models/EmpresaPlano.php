<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmpresaPlano extends Model
{
    use HasAuditColumns, HasFactory;

    protected $table = 'empresa_planos';

    protected $fillable = [
        'empresa_id',
        'plano_id',
        'proximo_plano_id',
        'periodicidade',
        'proxima_periodicidade',
        'status',
        'mudanca_tipo',
        'metodo_cobranca',
        'data_inicio',
        'data_vencimento',
        'mudanca_efetiva_em',
        'mudanca_solicitada_em',
        'data_renovacao',
        'dia_vencimento',
        'renovacao_automatica',
        'cartao_ultimos_digitos',
        'cartao_bandeira',
        'valor_cobrado',
        'gateway_cobranca',
        'gateway_subscription_id',
        'gateway_customer_id',
        'metadados',
        'criado_por_user_id',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_vencimento' => 'date',
        'mudanca_efetiva_em' => 'date',
        'mudanca_solicitada_em' => 'datetime',
        'data_renovacao' => 'date',
        'valor_cobrado' => 'decimal:2',
        'renovacao_automatica' => 'boolean',
        'metadados' => 'array',
        'datainc' => 'datetime',
        'datatu' => 'datetime',
    ];

    public function plano(): BelongsTo
    {
        return $this->belongsTo(Plano::class, 'plano_id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function proximoPlano(): BelongsTo
    {
        return $this->belongsTo(Plano::class, 'proximo_plano_id');
    }

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por_user_id');
    }

    public function pagamentos(): HasMany
    {
        return $this->hasMany(EmpresaPlanoPagamento::class, 'empresa_plano_id')->orderByDesc('data_vencimento');
    }

    public function estaAtivo(): bool
    {
        if (! in_array($this->status, ['ativo', 'trial'], true)) {
            return false;
        }

        if ($this->data_vencimento === null) {
            return true;
        }

        return ! $this->data_vencimento->isPast();
    }

    public function estaInadimplente(): bool
    {
        return $this->status === 'inadimplente';
    }

    public function diasParaVencer(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->data_vencimento, false);
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'ativo' => ['label' => 'Ativo',               'class' => 'badge-success'],
            'trial' => ['label' => 'Trial',               'class' => 'badge-info'],
            'expirado' => ['label' => 'Expirado',            'class' => 'badge-dark'],
            'inadimplente' => ['label' => 'Inadimplente',        'class' => 'badge-danger'],
            'cancelado' => ['label' => 'Cancelado',           'class' => 'badge-secondary'],
            'aguardando_pagamento' => ['label' => 'Aguardando Pagamento', 'class' => 'badge-warning'],
            default => ['label' => ucfirst($this->status), 'class' => 'badge-secondary'],
        };
    }

    public function getPeriodicidadeTextoAttribute(): string
    {
        return $this->periodicidade === 'anual' ? 'Anual' : 'Mensal';
    }

    public function temMudancaAgendada(): bool
    {
        return $this->mudanca_tipo !== null && $this->mudanca_efetiva_em !== null;
    }
}
