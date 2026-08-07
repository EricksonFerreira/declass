<?php

namespace App\Modules\Fiscal\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MotorTributarioService
{
    public function calcular(
        float $valor,
        string $tipoOperacao,
        Carbon $data
    ): array {
        $regras = DB::table('regras_tributarias')
            ->where('tipo_operacao', $tipoOperacao)
            ->where('data_inicio', '<=', $data->toDateString())
            ->where(function ($query) use ($data) {
                $query->whereNull('data_fim')
                    ->orWhere('data_fim', '>=', $data->toDateString());
            })
            ->where('ativo', true)
            ->get();

        $tributos = [];

        foreach ($regras as $regra) {
            $valorTributo = round($valor * ($regra->aliquota / 100), 2);

            $tributos[] = [
                'tributo'      => $regra->tributo,
                'base_calculo' => $valor,
                'aliquota'     => (float) $regra->aliquota,
                'valor'        => $valorTributo,
                'cst'          => $regra->gera_credito ? '01' : '03',
            ];
        }

        return $tributos;
    }
}
