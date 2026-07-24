<?php

namespace App\Actions\Reserva\Renovacao;

use App\Models\Reserva;
use App\Models\Transacao;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\ReservaService;

class RenovarReservaComumAction implements RenovacaoStrategyInterface
{
    public function __construct(
        protected ReservaService $reservaService
    ) {}

    /**
     * Executa a renovação de uma reserva comum (não MAP).
     *
     * @param  Request $request
     * @param  Reserva $reserva
     * @return Reserva
     */
    public function handle(Request $request, mixed $reserva): mixed
    {
        $request->validate([
            'data_fim_nova'          => 'required|date',
            'hora_fim_nova'          => 'required|string',
            'estacao_devolucao_id'   => 'required|integer|exists:estacoes,id',
            'observacoes'            => 'nullable|string|max:1000',
        ]);

        $novaDataHora = Carbon::parse(
            $request->input('data_fim_nova') . ' ' . $request->input('hora_fim_nova')
        );
        $dataFimAtual = Carbon::parse($reserva->data_fim);

        if ($novaDataHora->lte($dataFimAtual)) {
            throw new \RuntimeException(
                'A nova data de devolução deve ser posterior à data atual de devolução ('
                . $dataFimAtual->format('d/m/Y H:i') . ').'
            );
        }

        DB::beginTransaction();

        try {
            $diasExtras     = max(1, (int) ceil($novaDataHora->diffInDays($dataFimAtual)));
            $tarifaBase     = (float) $reserva->tarifa_base;
            $valorAdicional = round($diasExtras * $tarifaBase, 2);

            $reserva->data_fim               = $novaDataHora->format('Y-m-d H:i:s');
            $reserva->estacao_devolucao_id   = (int) $request->input('estacao_devolucao_id');
            $reserva->quantidade_dias       += $diasExtras;
            $reserva->valor_total_tarifa_base += $valorAdicional;
            $reserva->valor_total_final      += $valorAdicional;
            $reserva->save();

            $payloadTransacao = [
                'user_id'          => $reserva->user_id,
                'reserva_id'       => $reserva->id,
                'valor'            => $valorAdicional,
                'moeda'            => $reserva->empresa?->moeda_pagamento ?? 'BRL',
                'metodo_pagamento' => 'stripe',
                'status'           => 'pending',
                'info_pagamento'   => [
                    'descricao' => 'Cobrança de Renovação/Aditivo: +' . $diasExtras . ' diárias',
                ],
            ];

            if (\App\Helpers\SchemaCache::hasColumn('transacoes', 'tipo_pagamento_id')) {
                $payloadTransacao['tipo_pagamento_id'] = 1;
            }

            Transacao::create($payloadTransacao);

            $msgLog = 'Reserva renovada. Nova data de devolução: '
                . $novaDataHora->format('d/m/Y H:i')
                . '. Adicionado ' . $diasExtras
                . ' diárias (R$ ' . number_format($valorAdicional, 2, ',', '.') . ').';

            $this->reservaService->adicionaLog(
                $reserva->id,
                Auth::id(),
                [
                    'dias_extras'     => $diasExtras,
                    'valor_adicional' => $valorAdicional,
                    'nova_data_fim'   => $novaDataHora->format('Y-m-d H:i:s'),
                ],
                $msgLog,
                'success'
            );

            DB::commit();

            return $reserva;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
