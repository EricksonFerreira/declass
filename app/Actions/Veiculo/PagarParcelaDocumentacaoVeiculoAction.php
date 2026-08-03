<?php

namespace App\Actions\Veiculo;

use App\Models\DocumentacaoVeiculoParcela;
use App\Models\LancamentoFinanceiro;
use Illuminate\Support\Facades\DB;

class PagarParcelaDocumentacaoVeiculoAction
{
    public function handle(DocumentacaoVeiculoParcela $parcela, string $dataPagamento): DocumentacaoVeiculoParcela
    {
        return DB::transaction(function () use ($parcela, $dataPagamento) {
            $dataPagamento = date('Y-m-d', strtotime($dataPagamento));

            $parcela->update([
                'status' => 'pago',
                'data_pagamento' => $dataPagamento,
            ]);

            LancamentoFinanceiro::query()
                ->where('documentacao_veiculo_id', $parcela->documentacao_veiculo_id)
                ->where('parcela_atual', $parcela->numero_parcela)
                ->where('tipo', 'despesa')
                ->update([
                    'status' => 'pago',
                    'data_pagamento' => $dataPagamento,
                ]);

            return $parcela->fresh();
        });
    }
}
