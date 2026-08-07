<?php

namespace App\Modules\Fiscal\Actions;

use App\Models\CreditoTributario;
use Carbon\Carbon;

class RegistrarCreditoTributarioAction
{
    public function handle(array $data): CreditoTributario
    {
        $valorOriginal = (float) $data['valor_original'];
        
        return CreditoTributario::create([
            'empresa_id' => $data['empresa_id'],
            'documento_fiscal_entrada_id' => $data['documento_fiscal_entrada_id'] ?? null,
            'tributo' => $data['tributo'],
            'valor_original' => $valorOriginal,
            'valor_utilizado' => 0.00,
            'saldo' => $valorOriginal,
            'origem' => $data['origem'],
            'data_apropriacao' => $data['data_apropriacao'] ?? now(),
            'status' => 'DISPONIVEL',
        ]);
    }
}
