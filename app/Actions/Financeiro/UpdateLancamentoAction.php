<?php

namespace App\Actions\Financeiro;

use App\Models\LancamentoFinanceiro;

class UpdateLancamentoAction
{
    public function handle(LancamentoFinanceiro $lancamento, array $data): LancamentoFinanceiro
    {
        $lancamento->update($data);
        return $lancamento;
    }
}
