<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmpresaPlano extends Model
{
    protected $table = 'empresa_planos';

    protected $fillable = [
        'empresa_id',
        'plano_id',
        'periodicidade',
        'status',
        'metodo_cobranca',
        'data_inicio',
        'data_vencimento',
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
    ];

    protected $casts = [
        'data_inicio'     => 'date',
        'data_vencimento' => 'date',
        'data_renovacao'  => 'date',
        'valor_cobrado'        => 'decimal:2',
        'renovacao_automatica' => 'boolean',
        'metadados'            => 'array',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function plano(): BelongsTo
    {
        return $this->belongsTo(Plano::class, 'plano_id');
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
        return in_array($this->status, ['ativo', 'trial'], true);
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
            'ativo'               => ['label' => 'Ativo',               'class' => 'badge-success'],
            'trial'               => ['label' => 'Trial',               'class' => 'badge-info'],
            'inadimplente'        => ['label' => 'Inadimplente',        'class' => 'badge-danger'],
            'cancelado'           => ['label' => 'Cancelado',           'class' => 'badge-secondary'],
            'aguardando_pagamento'=> ['label' => 'Aguardando Pagamento','class' => 'badge-warning'],
            default               => ['label' => ucfirst($this->status), 'class' => 'badge-secondary'],
        };
    }

    public function getPeriodicidadeTextoAttribute(): string
    {
        return $this->periodicidade === 'anual' ? 'Anual' : 'Mensal';
    }
}
