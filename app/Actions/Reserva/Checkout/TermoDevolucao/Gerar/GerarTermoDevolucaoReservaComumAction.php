<?php

namespace App\Actions\Reserva\Checkout\TermoDevolucao\Gerar;

use App\Models\Reserva;
use App\Services\PdfService;
use Illuminate\Support\Facades\Storage;

class GerarTermoDevolucaoReservaComumAction
{
    public function __construct(
        private PdfService $pdfService,
    ) {}

    public function handle(Reserva $reserva): string
    {
        // Generate PDF and save
        $filename = 'contratos/checkout_' . $reserva->id . '_' . time() . '.pdf';
        return $filename;
    }
}
