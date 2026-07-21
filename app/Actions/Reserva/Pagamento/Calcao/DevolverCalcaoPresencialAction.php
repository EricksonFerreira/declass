<?php

namespace App\Actions\Reserva\Pagamento\Calcao;

use App\Models\Calcao;
use App\Models\Reserva;
use App\Models\Transacao;
use App\Services\ReservaService;
use App\Support\SchemaCache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DevolverCalcaoPresencialAction
{
    public function __construct(
        private ReservaService $reservaService,
    ) {}

    public function handle(Reserva $reserva, ?string $observacaoInput): Calcao
    {
        $calcao = $reserva->calcao;
        if (!$calcao) {
            throw new \RuntimeException('Calção não encontrado para esta reserva.');
        }

        if (!empty($calcao->payment_intent_id)) {
            throw new \InvalidArgumentException('Este calção foi registrado via Stripe. Use "Liberar Hold" ou fluxo de captura/liberação.');
        }

        if ($calcao->status === Calcao::STATUS_CANCELADO) {
            throw new \RuntimeException('Calção já devolvido.');
        }

        $valor = (float) ($calcao->valor_capturado ?: $calcao->valor_autorizado ?: $calcao->valor_calculado ?: 0);
        if ($valor <= 0) {
            throw new \InvalidArgumentException('Valor inválido para devolução.');
        }

        $observacao = trim((string) ($observacaoInput ?: 'Devolução do calção presencial no checkout'));
        $metodo = (string) (($calcao->stripe_response['metodo'] ?? null) ?: 'presencial');

        return DB::transaction(function () use ($reserva, $calcao, $valor, $observacao, $metodo) {
            $calcao->update([
                'status' => Calcao::STATUS_CANCELADO,
                'data_hora_cancelamento' => now(),
                'usuario_responsavel_id' => Auth::id(),
                'observacao' => $observacao,
            ]);

            $payload = [
                'user_id' => $reserva->user_id,
                'pago_por_user_id' => Auth::id(),
                'reserva_id' => $reserva->id,
                'valor' => -1 * abs($valor),
                'moeda' => 'BRL',
                'metodo_pagamento' => $metodo,
                'status' => 'refunded',
                'info_pagamento' => [
                    'tipo' => 'calcao',
                    'acao' => 'devolvido_presencial',
                    'calcao_id' => $calcao->id,
                ],
            ];
            if (SchemaCache::hasColumn('transacoes', 'confirmado_em')) {
                $payload['confirmado_em'] = now();
            }
            if (SchemaCache::hasColumn('transacoes', 'confirmado_por_user_id')) {
                $payload['confirmado_por_user_id'] = Auth::id();
            }
            if (SchemaCache::hasColumn('transacoes', 'empresa_id')) {
                $payload['empresa_id'] = $reserva->empresa_id ?? null;
            }

            Transacao::create($payload);

            $this->reservaService->adicionaLog((int) $reserva->id, Auth::id(), json_encode([
                'acao' => 'Calção presencial devolvido',
                'calcao_id' => $calcao->id,
                'valor' => $valor,
            ]));

            return $calcao;
        });
    }
}
