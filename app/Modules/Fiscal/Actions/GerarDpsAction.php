<?php

namespace App\Modules\Fiscal\Actions;

use App\Models\OperacaoFiscal;
use App\Modules\Fiscal\DTO\DpsDTO;

class GerarDpsAction
{
    public function handle(OperacaoFiscal $operacao): DpsDTO
    {
        $dps = new DpsDTO();
        $dps->prestador = [
            'CNPJ' => $operacao->empresa->cnpj ?? '',
            'IM' => $operacao->empresa->nfse_inscricao_municipal ?? null,
        ];

        $usuario = $operacao->locacao ? $operacao->locacao->usuario : null;
        $doc = $usuario?->numero_identificacao ?? '';
        $dps->tomador = [
            'CPF' => strlen($doc) === 11 ? $doc : null,
            'CNPJ' => strlen($doc) === 14 ? $doc : null,
            'xNome' => $usuario?->nome ?? 'Consumidor',
            'email' => $usuario?->email ?? null,
        ];

        $dps->codigoTributacao = '99.04.01'; // Default Locacao de Bens Moveis
        $dps->valorOperacao = $operacao->valor_operacao;

        $ibs = $operacao->tributos()->where('tributo', 'IBS')->sum('valor');
        $cbs = $operacao->tributos()->where('tributo', 'CBS')->sum('valor');
        $dps->ibs = $ibs;
        $dps->cbs = $cbs;

        $veiculo = $operacao->locacao ? $operacao->locacao->veiculo : null;
        $ncm = '87032310';
        $descricao = 'Locacao de veiculo';
        if ($veiculo) {
            $classificacao = $veiculo->classificacaoFiscal ?? null;
            $ncm = $classificacao?->ncm ?: '87032310';
            $descricao = $veiculo->marca . ' ' . $veiculo->modelo;
        }

        $dps->bens[] = [
            'ncm' => $ncm,
            'descricao' => $descricao,
            'quantidade' => 1,
        ];

        return $dps;
    }
}
