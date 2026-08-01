<?php

namespace App\Actions\Reserva\Checkin\Contrato\Exibir;

use App\Models\Reserva;
use App\Models\ReservaMotoristaApp;
use App\Services\PdfService;

class ExibirContratoCheckinMotoristaAppAction
{
    public function __construct(
        private PdfService $pdfService,
        private ExibirContratoCheckinReservaComumAction $comumAction,
    ) {}

    public function gerarPdf(ReservaMotoristaApp $reserva): string
    {
        $contratoId = (int) data_get($reserva->documentos_json, 'checkin.contrato_checkin_id', 0);
        $reservaOpId = (int) ($reserva->codigoReserva?->reserva_id ?? data_get($reserva->documentos_json, 'checkin.reserva_operacional_id', 0));

        $contratoCheckin = null;
        if ($contratoId > 0) {
            $contratoCheckin = \App\Models\ContratoCheckin::find($contratoId);
        }

        if (!$contratoCheckin) {
            $contratoCheckin = \App\Models\ContratoCheckin::where(function ($q) use ($reserva, $reservaOpId) {
                $q->where('reserva_id', (int) $reserva->id);
                if ($reservaOpId > 0) {
                    $q->orWhere('reserva_id', $reservaOpId);
                }
            })
            ->latest('id')
            ->first();
        }

        if ($contratoCheckin) {
            $reservaDummy = $contratoCheckin->reserva;
            if (!$reservaDummy) {
                $reservaDummy = new Reserva();
                $reservaDummy->id = $reserva->id;
                $reservaDummy->empresa_id = $reserva->empresa_id;
                $reservaDummy->user_id = $reserva->user_id;
                $reservaDummy->veiculo_id = $contratoCheckin->veiculo_id;
                $reservaDummy->estacao_retirada_id = $reserva->estacao_retirada_id;
                $reservaDummy->data_inicio = $reserva->data_inicio;
                $reservaDummy->data_fim = $reserva->data_fim;
                $reservaDummy->quantidade_dias = $reserva->quantidade_dias;
                $reservaDummy->setRelation('contratoCheckin', $contratoCheckin);
                $reservaDummy->setRelation('empresa', $reserva->empresa);
                $reservaDummy->setRelation('usuario', $reserva->user);
                $reservaDummy->setRelation('veiculo', $contratoCheckin->veiculo ?? $reserva->veiculo);
            }

            $data = $this->comumAction->montarDados($reservaDummy);
            return $this->pdfService->gerarConteudo(
                view: 'pdf.contratos.checkin.motorista-app',
                data: $data,
            );
        }

        throw new \RuntimeException('Contrato de check-in de motorista de aplicativo ainda não foi gerado.');
    }
}
