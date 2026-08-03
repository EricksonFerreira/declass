<?php

namespace App\Actions\Reserva\Checkout\TermoDevolucao\Assinar;

use App\Actions\Reserva\Checkout\TermoDevolucao\Exibir\ExibirTermoDevolucaoReservaComumAction;
use App\Models\Reserva;
use App\Support\ArquivoAnexoHelper;
use App\Services\ReservaService;
use App\Services\PdfService;
use Illuminate\Support\Facades\Storage;

class AssinarTermoDevolucaoReservaComumAction
{
    public function __construct(
        private PdfService $pdfService,
        private ExibirTermoDevolucaoReservaComumAction $verContratoAction,
        private ReservaService $reservaService,
    ) {}

    public function handle(Reserva $reserva, string $assinaturaBase64): void
    {
        $checkout = $reserva->contratoCheckout;
        if (! $checkout) {
            throw new \RuntimeException('Check-out nÃ£o encontrado para esta reserva.');
        }

        $checkout->assinatura = $assinaturaBase64;
        $checkout->data_assinatura = now();
        $checkout->assinado = 'S';
        $checkout->save();

        if (preg_match('/^data:image\/png;base64,/', $assinaturaBase64)) {
            $base64 = substr($assinaturaBase64, strpos($assinaturaBase64, ',') + 1);
            $assinaturaBinaria = base64_decode($base64, true);
            if ($assinaturaBinaria !== false) {
                ArquivoAnexoHelper::salvarConteudoRastreado(
                    $assinaturaBinaria,
                    'assinaturas/checkout/reserva_' . (int) $reserva->id,
                    [
                        'empresa_id' => $reserva->empresa_id ?? null,
                        'user_id' => auth()->id(),
                        'modulo' => 'checkout',
                        'entidade' => 'contrato_checkout',
                        'entidade_id' => $checkout->id,
                        'campo' => 'assinatura',
                        'contexto' => 'checkout-assinatura',
                        'mime_type' => 'image/png',
                    ],
                    'assinatura_' . $reserva->id . '.png'
                );
            }
        }

        $pdfContent = $this->verContratoAction->gerarPdf($reserva->fresh([
            'usuario', 'empresa', 'veiculo', 'grupoFrota', 'estacaoRetirada', 'estacaoDevolucao',
            'opcionais', 'contratoCheckin', 'modelos', 'statusReserva', 'cupomDesconto',
            'transacoes.user', 'transacoes.pagoPor', 'contratoCheckin.veiculo.marca',
            'contratoCheckin.veiculo.modelo', 'contratoCheckin.veiculo.grupoFrota',
            'contratoCheckin.marca', 'contratoCheckin.modelo',
            'veiculoDanos.dano.categoria', 'veiculoDanos.imagem', 'veiculoDanos.usuarioCorrecao',
            'avariaCobrancas', 'calcao', 'contratoCheckout',
        ]));

        $arquivo = 'contratos/checkout/checkout_' . $reserva->id . '_' . time() . '.pdf';
        Storage::disk('public')->put($arquivo, $pdfContent);
        ArquivoAnexoHelper::salvarConteudoRastreado(
            $pdfContent,
            'contratos/checkout',
            [
                'empresa_id' => $reserva->empresa_id ?? null,
                'user_id' => auth()->id(),
                'modulo' => 'checkout',
                'entidade' => 'contrato_checkout',
                'entidade_id' => $checkout->id,
                'campo' => 'pdf_path',
                'contexto' => 'checkout-pdf',
                'mime_type' => 'application/pdf',
            ],
            basename($arquivo)
        );
        $checkout->pdf_path = $arquivo;
        $checkout->save();

        $this->reservaService->adicionaLog(
            (int) $reserva->id,
            (int) auth()->id(),
            [
                'acao' => 'Termo de devolução assinado',
                'contrato_checkout_id' => $checkout->id,
                'pdf_path' => $checkout->pdf_path,
                'data_assinatura' => optional($checkout->data_assinatura)->toDateTimeString(),
                'signatario_nome' => $checkout->signatario_nome,
            ],
            'Termo de devoluçãhttp://xpandcars.test/admin/assinatura/35o assinado',
            'success'
        );

        $this->reservaService->adicionaLog(
            (int) $reserva->id,
            (int) auth()->id(),
            [
                'acao' => 'Checkout colocado em conferência',
                'contrato_checkout_id' => $checkout->id,
                'status_operacional' => 'Em conferência',
            ],
            'Checkout em conferência',
            'info'
        );
    }
}
