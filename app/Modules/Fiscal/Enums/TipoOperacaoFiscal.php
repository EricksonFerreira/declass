<?php

namespace App\Modules\Fiscal\Enums;

enum TipoOperacaoFiscal: string
{
    case LOCACAO_VEICULO = 'LOCACAO_VEICULO';
    case AQUISICAO_VEICULO = 'AQUISICAO_VEICULO';
    case MANUTENCAO_VEICULO = 'MANUTENCAO_VEICULO';
    case VENDA_VEICULO = 'VENDA_VEICULO';
}
