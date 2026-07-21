<?php

namespace App\Actions\Reserva\Checkin\Contrato\Gerar;

use App\Actions\Reserva\Checkin\Contrato\Exibir\ExibirContratoCheckinMotoristaAppAction;
use App\Models\ReservaMotoristaApp;
use Illuminate\Support\Facades\Storage;

class GerarContratoCheckinMotoristaAppAction
{
    public function __construct(
        private ExibirContratoCheckinMotoristaAppAction $exibirAction,
    ) {}

    public function handle(ReservaMotoristaApp $reserva): string
    {
        $pdfContent = $this->exibirAction->gerarPdf($reserva);
        $filename = 'contratos/checkin_' . $reserva->id . '_' . time() . '.pdf';
        Storage::disk('public')->put($filename, $pdfContent);
        return $filename;
    }
}
