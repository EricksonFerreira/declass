<?php

namespace App\Actions\Reserva\Checkout\TermoDevolucao\Assinar;

use App\Actions\Reserva\Checkout\TermoDevolucao\Exibir\ExibirTermoDevolucaoMotoristaAppAction;
use App\Support\ArquivoAnexoHelper;
use App\Models\ReservaMotoristaApp;
use Illuminate\Support\Facades\Storage;

class AssinarTermoDevolucaoMotoristaAppAction
{
    public function __construct(
        private ExibirTermoDevolucaoMotoristaAppAction $exibirTermoDevolucaoAction,
    ) {}

    public function handle(ReservaMotoristaApp $reserva, string $assinaturaBase64): void
    {
        if (!preg_match('/^data:image\/png;base64,/', $assinaturaBase64)) {
            throw new \RuntimeException('Assinatura inválida.');
        }

        $checkoutDocs = is_array($reserva->documentos_json ?? null)
            ? data_get($reserva->documentos_json, 'checkout', [])
            : [];

        data_set($checkoutDocs, 'assinatura', $assinaturaBase64);
        data_set($checkoutDocs, 'data_assinatura', now()->format('d/m/Y H:i'));
        data_set($checkoutDocs, 'assinado', 'S');

        $assinaturaBinaria = base64_decode(substr($assinaturaBase64, strpos($assinaturaBase64, ',') + 1), true);
        if ($assinaturaBinaria !== false) {
            $assinaturaPath = 'assinaturas/checkout/reserva-' . (int) $reserva->id . '-' . time() . '.png';
            Storage::disk('public')->put($assinaturaPath, $assinaturaBinaria);
            data_set($checkoutDocs, 'assinatura_path', $assinaturaPath);
        }

        data_set($reserva->documentos_json, 'checkout', $checkoutDocs);
        $reserva->save();

        $response = $this->exibirTermoDevolucaoAction->servirPdf($reserva);
        $pdfContent = $response?->getContent() ?? '';

        if ($pdfContent !== '') {
            $arquivo = 'contratos/checkout/checkout-motorista-app-' . (int) $reserva->id . '-' . time() . '.pdf';
            Storage::disk('public')->put($arquivo, $pdfContent);
            ArquivoAnexoHelper::salvarConteudoRastreado(
                $pdfContent,
                'contratos/checkout',
                [
                    'empresa_id' => $reserva->empresa_id ?? null,
                    'user_id' => auth()->id(),
                    'modulo' => 'checkout',
                    'entidade' => 'contrato_checkout',
                    'entidade_id' => $reserva->id,
                    'campo' => 'pdf_path',
                    'contexto' => 'checkout-pdf',
                    'mime_type' => 'application/pdf',
                ],
                basename($arquivo)
            );
            data_set($checkoutDocs, 'pdf_path', $arquivo);
            data_set($reserva->documentos_json, 'checkout', $checkoutDocs);
            $reserva->save();
        }
    }
}
