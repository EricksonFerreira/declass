<?php

namespace App\Actions\Reserva\Checkin\FinalizarMinhaReserva;

use App\DTO\Reserva\ReservaResolvida;
use App\Enums\TipoReservaEnum;
use App\Models\Reserva;
use Illuminate\Http\Request;

class FinalizarMinhaReservaCheckinMotoristaAppAction
{
    public function __construct(
        private FinalizarMinhaReservaCheckinReservaComumAction $comumAction,
    ) {}

    public function handle(ReservaResolvida $reserva, Request $request): mixed
    {
        $registro = $reserva->registro;

        $operacionalId = $registro->codigoReserva?->reserva_id
            ?? data_get($registro->documentos_json, 'checkin.reserva_operacional_id');

        $contratoId = (int) $request->input('contrato_id');
        if ($contratoId <= 0) {
            $contratoId = (int) data_get($registro->documentos_json, 'checkin.contrato_checkin_id');
        }
        if ($contratoId <= 0 && $operacionalId) {
            $contratoId = (int) \App\Models\ContratoCheckin::where('reserva_id', $operacionalId)->latest('id')->value('id');
        }

        $veiculoId = (int) $request->input('veiculo_id');
        if ($veiculoId <= 0) {
            $veiculoId = (int) (data_get($registro->documentos_json, 'checkin.veiculo_id') ?? $registro->veiculo_id);
        }

        $request->merge([
            'contrato_id' => $contratoId,
            'veiculo_id' => $veiculoId,
        ]);

        if ($operacionalId) {
            $reservaOperacional = Reserva::find($operacionalId);
            if ($reservaOperacional) {
                $reservaResolvida = new ReservaResolvida(
                    (string) $reservaOperacional->id,
                    TipoReservaEnum::COMUM,
                    $reservaOperacional
                );

                return $this->comumAction->handle($reservaResolvida, $request);
            }
        }

        // Fallback: Se for check-in direto no registro de motorista app
        $contratoCheckin = null;
        if ($contratoId > 0) {
            $contratoCheckin = \App\Models\ContratoCheckin::find($contratoId);
        }

        if (! $contratoCheckin) {
            $contratoCheckin = \App\Models\ContratoCheckin::where('reserva_id', (int) $registro->id)->latest('id')->first();
        }

        if ($contratoCheckin) {
            // Se o contrato estiver associado a uma Reserva comum existente
            if ($contratoCheckin->reserva) {
                $reservaResolvidaObj = new ReservaResolvida(
                    (string) $contratoCheckin->reserva_id,
                    TipoReservaEnum::COMUM,
                    $contratoCheckin->reserva
                );

                return $this->comumAction->handle($reservaResolvidaObj, $request);
            }

            // Caso o contrato pertença diretamente à ReservaMotoristaApp (sem registro na tabela 'reservas')
            $reservaDummy = new Reserva();
            $reservaDummy->id = $registro->id;
            $reservaDummy->empresa_id = $registro->empresa_id;
            $reservaDummy->estacao_retirada_id = $registro->estacao_retirada_id;
            $reservaDummy->user_id = $registro->user_id;

            $request->merge([
                'contrato_id' => $contratoCheckin->id,
                'veiculo_id' => $contratoCheckin->veiculo_id,
            ]);

            $reservaResolvidaObj = new ReservaResolvida(
                $reserva->codigo,
                TipoReservaEnum::COMUM,
                $reservaDummy
            );

            return $this->comumAction->handle($reservaResolvidaObj, $request);
        }

        throw new \RuntimeException('Contrato de check-in não encontrado para esta reserva de motorista de aplicativo.');
    }
}
