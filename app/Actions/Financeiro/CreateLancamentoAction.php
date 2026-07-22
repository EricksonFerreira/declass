<?php

namespace App\Actions\Financeiro;

use App\Models\LancamentoFinanceiro;

class CreateLancamentoAction
{
    public function handle(array $data): LancamentoFinanceiro
    {
        return LancamentoFinanceiro::create($data);
    }
}
