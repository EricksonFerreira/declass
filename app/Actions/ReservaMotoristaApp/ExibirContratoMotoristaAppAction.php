<?php

namespace App\Actions\ReservaMotoristaApp;

use App\Models\ReservaMotoristaApp;
use App\Services\PdfService;
use Illuminate\Support\Facades\Storage;

class ExibirContratoMotoristaAppAction
{
    public function __construct(private PdfService $pdfService) {}

    public function gerarPdf(ReservaMotoristaApp $reserva): string
    {
        if (!empty($reserva->contrato_snapshot_json)) {
            return $this->pdfService->gerarCheckinPdfContent((array) $reserva->contrato_snapshot_json);
        }
        throw new \RuntimeException('Contrato de check-in ainda não foi gerado.');
    }

    public function gerarPdfCheckout(ReservaMotoristaApp $reserva): string
    {
        if (!empty($reserva->contrato_checkout_snapshot_json)) {
            return $this->pdfService->gerarCheckinPdfContent((array) $reserva->contrato_checkout_snapshot_json);
        }
        throw new \RuntimeException('Contrato de check-out ainda não foi gerado.');
    }

    public function salvarPdf(ReservaMotoristaApp $reserva, string $campoPdf = 'contrato_pdf_path', string $campoSnapshot = 'contrato_snapshot_json'): string
    {
        $snapshot = $reserva->{$campoSnapshot};
        if (empty($snapshot)) {
            throw new \RuntimeException('Snapshot do contrato não encontrado.');
        }

        $pdf = $this->pdfService->gerarCheckinPdfContent((array) $snapshot);
        $arquivo = 'contratos/motorista-app/reserva-' . (int) $reserva->id . '-' . time() . '.pdf';
        Storage::disk('public')->put($arquivo, $pdf);
        $reserva->update([$campoPdf => $arquivo]);
        return $arquivo;
    }
}



