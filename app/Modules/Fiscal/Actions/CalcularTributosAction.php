<?php

namespace App\Modules\Fiscal\Actions;

use App\Models\OperacaoFiscal;
use App\Models\OperacaoFiscalTributo;
use App\Modules\Fiscal\Services\MotorTributarioService;
use Carbon\Carbon;

class CalcularTributosAction
{
    public function __construct(
        protected MotorTributarioService $motor
    ) {}

    public function handle(OperacaoFiscal $operacao): void
    {
        // Delete existing calculated taxes for this operation if any
        $operacao->tributos()->delete();

        // Calculate taxes using MotorTributarioService
        $tributos = $this->motor->calcular(
            $operacao->valor_base_calculo,
            $operacao->tipo,
            Carbon::parse($operacao->data_fato_gerador)
        );

        foreach ($tributos as $tributoData) {
            OperacaoFiscalTributo::create([
                'operacao_fiscal_id' => $operacao->id,
                'tributo' => $tributoData['tributo'],
                'cst' => $tributoData['cst'] ?? null,
                'base_calculo' => $tributoData['base_calculo'],
                'aliquota' => $tributoData['aliquota'],
                'valor' => $tributoData['valor'],
            ]);
        }

        $operacao->update(['status' => 'CALCULADA']);
    }
}
