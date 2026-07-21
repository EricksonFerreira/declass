<?php

namespace App\Actions\Reserva\Checkout\TermoDevolucao\Gerar;

use App\Models\ReservaMotoristaApp;
use App\Services\PdfService;

class GerarTermoDevolucaoMotoristaAppAction
{
    public function __construct(
        private PdfService $pdfService,
    ) {}

    public function handle(ReservaMotoristaApp $reserva): string
    {
        $filename = 'contratos/checkout_motorista_' . $reserva->id . '_' . time() . '.pdf';
        return $filename;
    }
}
