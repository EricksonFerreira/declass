<?php

namespace App\Actions\Reserva\Checkin\Contrato\Assinar;

use App\Models\ReservaMotoristaApp;
use App\Services\PdfService;
use Illuminate\Support\Facades\Storage;

class AssinarContratoCheckinMotoristaAppAction
{
    public function __construct(
        private PdfService $pdfService,
    ) {}

    public function handle(ReservaMotoristaApp $reserva, string $assinaturaBase64): void
    {
        if (empty($reserva->contrato_efetivado_em) || empty($reserva->contrato_snapshot_json)) {
            throw new \RuntimeException('Efetive a locacao antes de assinar o contrato.');
        }

        if (empty($reserva->checkin_realizado_em)) {
            throw new \RuntimeException('Registre o check-in antes de assinar o contrato.');
        }

        if (!preg_match('/^data:image\/png;base64,/', $assinaturaBase64)) {
            throw new \RuntimeException('Assinatura inválida.');
        }

        $assinaturaBinaria = base64_decode(substr($assinaturaBase64, strpos($assinaturaBase64, ',') + 1), true);
        if ($assinaturaBinaria === false) {
            throw new \RuntimeException('Nao foi possivel processar a assinatura.');
        }

        $assinaturaPath = 'assinaturas/motorista-app/reserva-' . (int) $reserva->id . '-' . time() . '.png';
        Storage::disk('public')->put($assinaturaPath, $assinaturaBinaria);

        $snapshot = (array) $reserva->contrato_snapshot_json;
        $dataAssinatura = now()->format('d/m/Y H:i');
        data_set($snapshot, 'assinatura', $assinaturaBase64);
        data_set($snapshot, 'documento.data_assinatura', $dataAssinatura);
        data_set($snapshot, 'contrato.assinado_em', $dataAssinatura);
        data_set($snapshot, 'contrato.assinatura_path', $assinaturaPath);

        $arquivoContrato = $reserva->contrato_pdf_path ?: 'contratos/motorista-app/reserva-' . (int) $reserva->id . '-' . time() . '.pdf';
        Storage::disk('public')->put(
            $arquivoContrato,
            $this->pdfService->gerarCheckinPdfContent($snapshot)
        );

        $reserva->update([
            'contrato_snapshot_json' => $snapshot,
            'contrato_pdf_path' => $arquivoContrato,
            'status' => 'ativa',
        ]);
    }
}
