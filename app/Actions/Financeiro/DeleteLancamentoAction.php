<?php

namespace App\Actions\Financeiro;

use App\Models\LancamentoFinanceiro;

class DeleteLancamentoAction
{
    public function handle(LancamentoFinanceiro $lancamento): bool
    {
        return $lancamento->delete();
    }
}
