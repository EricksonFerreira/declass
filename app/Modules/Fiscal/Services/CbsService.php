<?php

namespace App\Modules\Fiscal\Services;

class CbsService
{
    public function obterAliquotaPadrao(): float
    {
        return 8.8; // CBS standard rate, can be customized or loaded from rules
    }
}
