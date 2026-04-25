<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plano extends Model
{
    use HasFactory;
    protected $table = 'planos';

    protected $fillable = [
        'nome',
        'slug',
        'descricao',
        'valor_mensal',
        'valor_anual',
        'limite_veiculos',
        'site_vendas',
        'modulo_multas',
        'modulo_nfse',
        'multi_estacao',
        'api_integracao',
        'ativo',
        'destaque',
        'ordem',
    ];

    protected $casts = [
        'valor_mensal'   => 'decimal:2',
        'valor_anual'    => 'decimal:2',
        'site_vendas'    => 'boolean',
        'modulo_multas'  => 'boolean',
        'modulo_nfse'    => 'boolean',
        'multi_estacao'  => 'boolean',
        'api_integracao' => 'boolean',
        'ativo'          => 'boolean',
        'destaque'       => 'boolean',
    ];

    public function itens(): HasMany
    {
        return $this->hasMany(ItemPlano::class, 'plano_id')->orderBy('ordem');
    }

    public function assinaturas(): HasMany
    {
        return $this->hasMany(EmpresaPlano::class, 'plano_id');
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true)->orderBy('ordem');
    }

    public function getValorPorPeriodicidadeAttribute(): array
    {
        return [
            'mensal' => $this->valor_mensal,
            'anual'  => $this->valor_anual,
        ];
    }

    public function getDescontoAnualPercentualAttribute(): int
    {
        if ($this->valor_mensal <= 0) {
            return 0;
        }

        return (int) round((1 - ($this->valor_anual / $this->valor_mensal)) * 100);
    }

    public function getEconomiaAnualAttribute(): float
    {
        return round(($this->valor_mensal - $this->valor_anual) * 12, 2);
    }

    public function getLimiteVeiculosTextoAttribute(): string
    {
        return $this->limite_veiculos === null ? 'Ilimitado' : (string) $this->limite_veiculos;
    }
}
