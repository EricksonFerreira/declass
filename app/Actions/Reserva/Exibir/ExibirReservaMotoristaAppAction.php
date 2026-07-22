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

        $this->normalizarPagamentoQuitado($registro);

        $checkinDocs = is_array($registro->documentos_json ?? null)
            ? data_get($registro->documentos_json, 'checkin', [])
            : [];

        $contratoCheckin = null;
        $contratoCheckout = null;
        $calcaoMotoristaApp = null;
        $contratoId = (int) data_get($checkinDocs, 'contrato_checkin_id', 0);
        $reservaOpId = (int) data_get($checkinDocs, 'reserva_operacional_id', 0);

        if ($reservaOpId <= 0) {
            $reservaOpId = $this->localizarReservaOperacionalCalcao($registro);
        }

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

        if ($calcaoMotoristaApp && $reservaOpId > 0) {
            $this->sincronizarReservaOperacional($registro, $reservaOpId);
        }
        }

        if ($reservaOpId > 0) {
            $contratoCheckout = \App\Models\ContratoCheckout::with([
                'reserva.codigoReserva', 'veiculo.marca', 'veiculo.modelo',
            ])
            ->where('reserva_id', $reservaOpId)
            ->latest('id')
            ->first();
        }

        if (!$contratoCheckout) {
            $contratoCheckout = \App\Models\ContratoCheckout::with([
                'reserva.codigoReserva', 'veiculo.marca', 'veiculo.modelo',
            ])
            ->where('reserva_id', (int) $registro->id)
            ->latest('id')
            ->first();
        }

        $calcaoMotoristaApp = \App\Models\Calcao::query()
            ->where(function ($query) use ($registro, $reservaOpId, $contratoCheckin) {
                $query->where('reserva_id', (int) $registro->id);
                if ($reservaOpId > 0) {
                    $query->orWhere('reserva_id', $reservaOpId);
                }
                if ($contratoCheckin) {
                    $query->orWhere('contrato_checkin_id', (int) $contratoCheckin->id);
                }
            })
            ->latest('id')
            ->first();

        $avariasMotoristaApp = collect();
        if ($registro->codigoReserva?->id) {
            $avariasMotoristaApp = \App\Models\VeiculoDanos::with(['imagens', 'dano.categoria', 'categoria'])
                ->where('reserva_codigo_id', (int) $registro->codigoReserva->id)
                ->orderByDesc('id')
                ->get();
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
            'contratoCheckoutMotoristaApp' => $contratoCheckout,
            'calcaoMotoristaApp' => $calcaoMotoristaApp,
            'avariasMotoristaApp' => $avariasMotoristaApp,
            'codigo' => $codigo,
            'termoAceite' => $termoAceite,
        ]);
    }

    private function normalizarPagamentoQuitado(\App\Models\ReservaMotoristaApp $registro): void
    {
        $pagamentos = collect($registro->pagamentos ?? []);
        $valorTotal = round(
            (float) ($registro->valor_periodo_contrato ?: $registro->valor_plano)
            + (float) ($registro->valor_opcionais ?? 0),
            2
        );

        if ($valorTotal <= 0) {
            return;
        }

        $totalPago = (float) $pagamentos
            ->filter(fn ($p) => strtolower((string) ($p->status ?? '')) === 'pago')
            ->sum(fn ($p) => (float) ($p->valor_total ?? $p->valor_unitario ?? $p->valor ?? 0));

        if (round($valorTotal - $totalPago, 2) > 0) {
            return;
        }

        if ((bool) $registro->pagamento_efetuado && (string) $registro->status_pagamento === 'pago') {
            return;
        }

        $registro->forceFill([
            'pagamento_efetuado' => true,
            'status_pagamento' => 'pago',
            'status' => in_array((string) $registro->status, ['pendente', 'pendente_analise', 'pendente_pagamento'], true)
                ? 'confirmada'
                : $registro->status,
        ])->save();

        $registro->setRelation('pagamentos', $registro->pagamentos);
    }

    private function localizarReservaOperacionalCalcao(\App\Models\ReservaMotoristaApp $registro): int
    {
        $codigo = (string) ($registro->codigoReserva?->codigo ?? '');
        if ($codigo === '') {
            return 0;
        }

        return (int) \App\Models\Reserva::withoutGlobalScopes()
            ->where('user_id', (int) $registro->user_id)
            ->where('empresa_id', (int) $registro->empresa_id)
            ->where('observacoes', 'like', '%' . $codigo . '%')
            ->whereHas('calcao')
            ->latest('id')
            ->value('id');
    }

    private function sincronizarReservaOperacional(\App\Models\ReservaMotoristaApp $registro, int $reservaOpId): void
    {
        $documentos = is_array($registro->documentos_json ?? null) ? $registro->documentos_json : [];

        if ((int) data_get($documentos, 'checkin.reserva_operacional_id', 0) === $reservaOpId) {
            return;
        }

        data_set($documentos, 'checkin.reserva_operacional_id', $reservaOpId);
        data_set($documentos, 'checkin.reserva_operacional_codigo', \App\Models\ReservaCodigo::query()
            ->where('reserva_id', $reservaOpId)
            ->value('codigo'));

        $registro->forceFill(['documentos_json' => $documentos])->save();
    }
}
