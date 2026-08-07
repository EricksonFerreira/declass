<?php

namespace App\Modules\Fiscal\Enums;

enum CstIbsCbs: string
{
    case OPERACAO_ALICOTA_NORMAL = '01';
    case OPERACAO_ALICOTA_ZERO = '02';
    case ISENTA_IMUNE = '03';
    case NAO_INCIDENCIA = '04';
    case SUSPENSAO = '05';
}
