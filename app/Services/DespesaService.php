<?php

namespace App\Services;

use App\Models\Despesa;
use Illuminate\Database\Eloquent\Model;

class DespesaService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Despesa $despesa)
    {
        $this->model = $despesa;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Despesa) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Despesa');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /** Baixa do contas a pagar: marca como paga com data e forma. */
    public function pagar(Despesa $despesa, ?string $formaPagamento = null, ?string $dataPagamento = null): void
    {
        if ($despesa->status !== 'prevista') {
            throw new \DomainException('Apenas despesas previstas podem ser baixadas.');
        }

        $despesa->update([
            'status' => 'paga',
            'data_pagamento' => $dataPagamento ?? now()->toDateString(),
            'forma_pagamento' => $formaPagamento,
        ]);
    }

    /** Cancela uma despesa prevista (nao apaga — preserva historico). */
    public function cancelar(Despesa $despesa): void
    {
        if ($despesa->status !== 'prevista') {
            throw new \DomainException('Apenas despesas previstas podem ser canceladas.');
        }

        $despesa->update(['status' => 'cancelada']);
    }

    /**
     * Totais para o fluxo de caixa / DRE: soma por status em um
     * intervalo de datas (por vencimento ou pagamento).
     */
    public function totaisPorPeriodo(?string $inicio = null, ?string $fim = null, string $campoData = 'data_vencimento'): array
    {
        $query = Despesa::query()
            ->selectRaw('status, SUM(valor) as total')
            ->groupBy('status');

        if ($inicio) {
            $query->whereDate($campoData, '>=', $inicio);
        }
        if ($fim) {
            $query->whereDate($campoData, '<=', $fim);
        }

        $totais = ['prevista' => 0.0, 'paga' => 0.0, 'cancelada' => 0.0];
        foreach ($query->get() as $row) {
            $totais[$row->status] = (float) $row->total;
        }

        return $totais;
    }
}
