<?php

namespace App\Services;

use App\Models\ContaReceber;
use Illuminate\Database\Eloquent\Model;

class ContaReceberService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(ContaReceber $contaReceber)
    {
        $this->model = $contaReceber;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof ContaReceber) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\ContaReceber');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /** Baixa do contas a receber: marca como recebida com data e forma. */
    public function receber(ContaReceber $conta, ?string $formaRecebimento = null, ?string $dataRecebimento = null): void
    {
        if ($conta->status !== 'prevista') {
            throw new \DomainException('Apenas contas previstas podem ser baixadas.');
        }

        $conta->update([
            'status' => 'recebida',
            'data_recebimento' => $dataRecebimento ?? now()->toDateString(),
            'forma_recebimento' => $formaRecebimento,
        ]);
    }

    /** Cancela uma conta prevista (nao apaga — preserva historico). */
    public function cancelar(ContaReceber $conta): void
    {
        if ($conta->status !== 'prevista') {
            throw new \DomainException('Apenas contas previstas podem ser canceladas.');
        }

        $conta->update(['status' => 'cancelada']);
    }

    /**
     * Totais para o fluxo de caixa / DRE: soma por status em um
     * intervalo de datas (por vencimento ou recebimento).
     */
    public function totaisPorPeriodo(?string $inicio = null, ?string $fim = null, string $campoData = 'data_vencimento'): array
    {
        $query = ContaReceber::query()
            ->selectRaw('status, SUM(valor) as total')
            ->groupBy('status');

        if ($inicio) {
            $query->whereDate($campoData, '>=', $inicio);
        }
        if ($fim) {
            $query->whereDate($campoData, '<=', $fim);
        }

        $totais = ['prevista' => 0.0, 'recebida' => 0.0, 'cancelada' => 0.0];
        foreach ($query->get() as $row) {
            $totais[$row->status] = (float) $row->total;
        }

        return $totais;
    }
}
