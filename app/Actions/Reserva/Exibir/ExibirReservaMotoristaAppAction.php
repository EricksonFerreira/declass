<?php

namespace App\Actions\Reserva\Exibir;

use App\DTO\Reserva\ReservaResolvida;

class ExibirReservaMotoristaAppAction
{
    public function handle(ReservaResolvida $reserva): mixed
    {
        $registro = $reserva->registro;
        $codigo = $reserva->codigo;

        app(\App\Services\ReservaService::class)->adicionaLog($registro->id, 0, [], '', 'info');

        $registro->load([
            'empresa', 'user', 'solicitacao.user', 'solicitacao.planoMotoristaApp',
            'planoMotoristaApp.opcionais', 'estacaoRetirada', 'veiculo.marca',
            'veiculo.modelo', 'codigoReserva', 'pagamentos', 'multas', 'sinistros',
        ]);

        $checkinDocs = is_array($registro->documentos_json ?? null)
            ? data_get($registro->documentos_json, 'checkin', [])
            : [];

        $contratoCheckin = null;
        $contratoId = (int) data_get($checkinDocs, 'contrato_checkin_id', 0);
        $reservaOpId = (int) data_get($checkinDocs, 'reserva_operacional_id', 0);

        if ($contratoId > 0) {
            $contratoCheckin = \App\Models\ContratoCheckin::with([
                'reserva.codigoReserva', 'veiculo.marca', 'veiculo.modelo', 'marca', 'modelo',
            ])->find($contratoId);
        }

        if (!$contratoCheckin) {
            $contratoCheckin = \App\Models\ContratoCheckin::with([
                'reserva.codigoReserva', 'veiculo.marca', 'veiculo.modelo', 'marca', 'modelo',
            ])
            ->where(function ($q) use ($registro, $reservaOpId) {
                $q->where('reserva_id', (int) $registro->id);
                if ($reservaOpId > 0) {
                    $q->orWhere('reserva_id', $reservaOpId);
                }
            })
            ->latest('id')
            ->first();
        }

        $termoAceite = null;
        if ($registro->empresa_id) {
            $termoAceite = \App\Models\TermoConsentimento::query()
                ->where('empresa_id', (int) $registro->empresa_id)
                ->where(function ($query) {
                    $query->whereNull('ativo')->orWhere('ativo', 'S');
                })
                ->where('tipo', 'aceito-motorista-app')
                ->orderBy('ordem')
                ->orderBy('id')
                ->first();
        }

        return view('reserva.show-motorista-app', [
            'registro' => $registro,
            'empresa' => $registro->empresa,
            'contratoCheckinMotoristaApp' => $contratoCheckin,
            'codigo' => $codigo,
            'termoAceite' => $termoAceite,
        ]);
    }
}
