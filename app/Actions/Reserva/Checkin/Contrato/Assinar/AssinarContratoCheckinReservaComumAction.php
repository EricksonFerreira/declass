<?php

namespace App\Actions\Reserva\Checkin\Contrato\Assinar;

use App\Actions\Reserva\Checkin\Contrato\Exibir\ExibirContratoCheckinReservaComumAction;
use App\Models\Reserva;
use App\Services\PdfService;
use Illuminate\Support\Facades\Storage;

class AssinarContratoCheckinReservaComumAction
{
    public function __construct(
        private PdfService $pdfService,
        private ExibirContratoCheckinReservaComumAction $verContratoAction,
    ) {}

    public function handle(Reserva $reserva, string $assinaturaBase64): void
    {
        $contrato = $reserva->contratoCheckin;
        if (!$contrato) {
            throw new \RuntimeException('Contrato de check-in não encontrado.');
        }

        $dadosAssinatura = $assinaturaBase64;
        $contrato->assinatura = $dadosAssinatura;
        $contrato->data_assinatura = now();
        $contrato->assinado = 'S';
        $contrato->save();

        $pdfContent = $this->verContratoAction->gerarPdf($reserva->fresh([
            'usuario', 'empresa', 'veiculo', 'grupoFrota', 'estacaoRetirada', 'estacaoDevolucao',
            'opcionais', 'contratoCheckin', 'modelos', 'statusReserva', 'cupomDesconto',
            'transacoes.user', 'transacoes.pagoPor', 'contratoCheckin.veiculo.marca',
            'contratoCheckin.veiculo.modelo', 'contratoCheckin.veiculo.grupoFrota',
            'contratoCheckin.marca', 'contratoCheckin.modelo',
            'veiculoDanos.dano.categoria', 'veiculoDanos.imagem', 'veiculoDanos.usuarioCorrecao',
            'avariaCobrancas', 'calcao', 'contratoCheckout',
        ]));

        $arquivo = 'contratos/checkin_' . $reserva->id . '_' . time() . '.pdf';
        Storage::disk('public')->put($arquivo, $pdfContent);
        $contrato->pdf_path = $arquivo;
        $contrato->save();
    }
}
