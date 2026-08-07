<?php

namespace App\Modules\Fiscal\Enums;

enum Tributo: string
{
    case IBS = 'IBS';
    case CBS = 'CBS';
    case PIS = 'PIS';
    case COFINS = 'COFINS';
}
