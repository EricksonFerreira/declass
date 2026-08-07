<?php

namespace App\Modules\Fiscal\Services;

class IbsService
{
    public function obterAliquotaPadrao(): float
    {
        return 17.7; // IBS standard rate, can be customized or loaded from rules
    }
}
