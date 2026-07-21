<?php

namespace App\Actions\Reserva\Checkout\TermoDevolucao\Exibir;

use App\Models\Reserva;
use App\Services\PdfService;
use Illuminate\Support\Facades\Storage;

class ExibirTermoDevolucaoReservaComumAction
{
    public function __construct(private PdfService $pdfService) {}

    public function servirPdf(Reserva $reserva): ?\Illuminate\Http\Response
    {
        if (!$reserva->contratoCheckout) {
            return null;
        }

        if ($reserva->contratoCheckout->pdf_path && Storage::disk('public')->exists($reserva->contratoCheckout->pdf_path)) {
            return response()->file(storage_path('app/public/' . $reserva->contratoCheckout->pdf_path), [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="termo-devolucao-' . $reserva->id . '.pdf"',
            ]);
        }

        return null;
    }
}
