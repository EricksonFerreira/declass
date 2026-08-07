<?php

namespace App\Modules\Fiscal\Actions;

use App\Models\OperacaoFiscal;
use App\Models\DocumentoFiscal;
use App\Modules\Fiscal\Services\NfseNacionalService;

class EmitirNfseAction
{
    public function __construct(
        protected NfseNacionalService $nfseService
    ) {}

    public function handle(OperacaoFiscal $operacao): DocumentoFiscal
    {
        return $this->nfseService->emitirParaOperacao($operacao);
    }
}
