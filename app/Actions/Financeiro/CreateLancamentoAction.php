<?php

namespace App\Actions\Financeiro;

use App\Models\LancamentoFinanceiro;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CreateLancamentoAction
{
    public function handle(array $data): LancamentoFinanceiro
    {
        $recorrente = (bool) ($data['recorrente'] ?? false);
        $recorrencia = $data['recorrencia'] ?? null;
        $parcelasTotais = isset($data['parcelas_totais']) ? (int) $data['parcelas_totais'] : null;

        if ($recorrente && $recorrencia && $parcelasTotais > 1) {
            $grupoId = 'rec_' . Str::random(16);
            $data['grupo_recorrencia_id'] = $grupoId;
            $data['parcelas_totais'] = $parcelasTotais;
            $data['parcela_atual'] = 1;

            $primeiro = LancamentoFinanceiro::create($data);

            $dataVencimentoBase = Carbon::parse($data['data_vencimento']);

            for ($i = 2; $i <= $parcelasTotais; $i++) {
                $newData = $data;
                $newData['parcela_atual'] = $i;
                
                // Calcula a data de vencimento da parcela
                if ($recorrencia === 'semanal') {
                    $dataVencimentoBase->addWeek();
                } elseif ($recorrencia === 'mensal') {
                    $dataVencimentoBase->addMonth();
                } elseif ($recorrencia === 'anual') {
                    $dataVencimentoBase->addYear();
                }

                $newData['data_vencimento'] = $dataVencimentoBase->toDateString();
                
                // Parcelas futuras começam como pendente por padrão
                $newData['status'] = 'pendente';
                $newData['data_pagamento'] = null;

                LancamentoFinanceiro::create($newData);
            }

            return $primeiro;
        }

        return LancamentoFinanceiro::create($data);
    }
}
