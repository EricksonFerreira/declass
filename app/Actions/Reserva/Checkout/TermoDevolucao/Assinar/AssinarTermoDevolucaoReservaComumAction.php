<?php

namespace App\Actions\Reserva\Checkout\TermoDevolucao\Assinar;

use App\Actions\Reserva\Checkin\Contrato\Exibir\ExibirContratoCheckinReservaComumAction;
use App\Models\Reserva;
use App\Services\PdfService;
use Illuminate\Support\Facades\Storage;

class AssinarTermoDevolucaoReservaComumAction
{
    public function __construct(
        private PdfService $pdfService,
        private ExibirContratoCheckinReservaComumAction $verContratoAction,
    ) {}

    public function handle(Reserva $reserva, string $assinaturaBase64): void
    {
        $checkout = $reserva->contratoCheckout;
        if (! $checkout) {
            throw new \RuntimeException('Check-out não encontrado para esta reserva.');
        }

        $checkout->assinatura = $assinaturaBase64;
        $checkout->data_assinatura = now();
        $checkout->assinado = 'S';
        $checkout->save();

        $pdfContent = $this->verContratoAction->gerarPdf($reserva->fresh([
            'usuario', 'empresa', 'veiculo', 'grupoFrota', 'estacaoRetirada', 'estacaoDevolucao',
            'opcionais', 'contratoCheckin', 'modelos', 'statusReserva', 'cupomDesconto',
            'transacoes.user', 'transacoes.pagoPor', 'contratoCheckin.veiculo.marca',
            'contratoCheckin.veiculo.modelo', 'contratoCheckin.veiculo.grupoFrota',
            'contratoCheckin.marca', 'contratoCheckin.modelo',
            'veiculoDanos.dano.categoria', 'veiculoDanos.imagem', 'veiculoDanos.usuarioCorrecao',
            'avariaCobrancas', 'calcao', 'contratoCheckout',
        ]));

        $arquivo = 'contratos/checkout_'.$reserva->id.'_'.time().'.pdf';
        Storage::disk('public')->put($arquivo, $pdfContent);
        $checkout->pdf_path = $arquivo;
        $checkout->save();
    }
}
