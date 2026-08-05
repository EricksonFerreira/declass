<?php

namespace App\Actions\Reserva\Checkin\Contrato\Assinar;

use App\Actions\Reserva\Checkin\Contrato\Exibir\ExibirContratoCheckinReservaComumAction;
use App\Models\Reserva;
use App\Support\ArquivoAnexoHelper;
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

        $reserva->forceFill([
            'checkin_assinado_em' => now(),
        ])->save();

        if (preg_match('/^data:image\/png;base64,/', $assinaturaBase64)) {
            $base64 = substr($assinaturaBase64, strpos($assinaturaBase64, ',') + 1);
            $assinaturaBinaria = base64_decode($base64, true);
            if ($assinaturaBinaria !== false) {
                ArquivoAnexoHelper::salvarConteudoRastreado(
                    $assinaturaBinaria,
                    'assinaturas/checkin/reserva_' . (int) $reserva->id,
                    [
                        'empresa_id' => $reserva->empresa_id ?? null,
                        'user_id' => auth()->id(),
                        'modulo' => 'checkin',
                        'entidade' => 'contrato_checkin',
                        'entidade_id' => $contrato->id,
                        'campo' => 'assinatura',
                        'contexto' => 'checkin-assinatura',
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

        $arquivo = 'contratos/checkin/checkin_' . $reserva->id . '_' . time() . '.pdf';
        Storage::disk('public')->put($arquivo, $pdfContent);
        ArquivoAnexoHelper::salvarConteudoRastreado(
            $pdfContent,
            'contratos/checkin',
            [
                'empresa_id' => $reserva->empresa_id ?? null,
                'user_id' => auth()->id(),
                'modulo' => 'checkin',
                'entidade' => 'contrato_checkin',
                'entidade_id' => $contrato->id,
                'campo' => 'pdf_path',
                'contexto' => 'checkin-pdf',
                'mime_type' => 'application/pdf',
            ],
            basename($arquivo)
        );
        $contrato->pdf_path = $arquivo;
        $contrato->save();
    }
}
