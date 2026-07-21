<?php

namespace App\Actions\Reserva\Checkin\Contrato\Gerar;

use App\Actions\Reserva\Checkin\Contrato\Exibir\ExibirContratoCheckinReservaComumAction;
use App\Models\Reserva;
use Illuminate\Support\Facades\Storage;

class GerarContratoCheckinReservaComumAction
{
    public function __construct(
        private ExibirContratoCheckinReservaComumAction $exibirAction,
    ) {}

    public function handle(Reserva $reserva): string
    {
        $pdfContent = $this->exibirAction->gerarPdf($reserva);
        $filename = 'contratos/checkin_' . $reserva->id . '_' . time() . '.pdf';
        Storage::disk('public')->put($filename, $pdfContent);

        if ($reserva->contratoCheckin) {
            $reserva->contratoCheckin->update(['pdf_path' => $filename]);
        }

        return $filename;
    }
}
