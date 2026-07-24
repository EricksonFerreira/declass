<?php

namespace App\Actions\Reserva\Checkout\TermoDevolucao\Exibir;

use App\Models\Reserva;
use App\Services\PdfService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ExibirTermoDevolucaoReservaComumAction
{
    public function __construct(
        private PdfService $pdfService
    ) {}

    public function servirPdf(Reserva $reserva): ?Response
    {
        $contratoCheckout = $reserva->contratoCheckout;

        if (! $contratoCheckout) {
            return null;
        }

        if (
            $contratoCheckout->pdf_path
            && Storage::disk('public')->exists($contratoCheckout->pdf_path)
        ) {
            $caminhoCompleto = Storage::disk('public')->path(
                $contratoCheckout->pdf_path
            );

            return response()->file($caminhoCompleto, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="termo-devolucao-'.$reserva->id.'.pdf"',
            ]);
        }

        $exibirCheckinAction = app(
            \App\Actions\Reserva\Checkin\Contrato\Exibir\ExibirContratoCheckinReservaComumAction::class
        );

        $data = $exibirCheckinAction->montarDados(
            $reserva,
            'checkout'
        );

        $pdf = $this->pdfService->gerarConteudo(
            view: 'pdf.contratos.checkout.comum',
            data: $data,
        );

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="termo-devolucao-'.$reserva->id.'.pdf"',
        ]);
    }
}
