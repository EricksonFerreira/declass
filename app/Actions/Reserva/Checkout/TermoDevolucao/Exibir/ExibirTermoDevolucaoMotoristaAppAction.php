<?php

namespace App\Actions\Reserva\Checkout\TermoDevolucao\Exibir;

use App\Models\ReservaMotoristaApp;
use App\Services\PdfService;
use Illuminate\Support\Facades\Storage;

class ExibirTermoDevolucaoMotoristaAppAction
{
    public function __construct(private PdfService $pdfService) {}

    public function servirPdf(ReservaMotoristaApp $reserva): ?\Illuminate\Http\Response
    {
        if (!empty($reserva->contrato_checkout_snapshot_json)) {
            $pdf = $this->pdfService->gerarCheckinPdfContent((array) $reserva->contrato_checkout_snapshot_json);
            return response($pdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="termo-devolucao-motorista-app-' . $reserva->id . '.pdf"',
            ]);
        }

        if ($reserva->contrato_pdf_path && Storage::disk('public')->exists($reserva->contrato_pdf_path)) {
            return response()->file(storage_path('app/public/' . $reserva->contrato_pdf_path), [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="termo-devolucao-motorista-app-' . $reserva->id . '.pdf"',
            ]);
        }

        return null;
    }
}
