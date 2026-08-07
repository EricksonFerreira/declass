<?php

namespace App\Modules\Fiscal\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\OperacaoFiscal;
use App\Modules\Fiscal\Actions\EmitirNfseAction;

class EmitirNfseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected int $operacaoFiscalId
    ) {}

    public function handle(EmitirNfseAction $action): void
    {
        $operacao = OperacaoFiscal::findOrFail($this->operacaoFiscalId);
        $action->handle($operacao);
    }
}
