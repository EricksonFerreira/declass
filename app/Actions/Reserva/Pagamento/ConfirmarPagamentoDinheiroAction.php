<?php

namespace App\Actions\Reserva\Pagamento;

use App\Models\Reserva;
use App\Models\Transacao;
use App\Services\ReservaService;
use Illuminate\Support\Facades\Auth;

class ConfirmarPagamentoDinheiroAction
{
    public function __construct(
        private ReservaService $reservaService,
    ) {}

    public function handle(Reserva $reserva, ?int $transacaoId, float $valor, string $metodoManual): Transacao
    {
        $metodosManuaisPermitidos = ['dinheiro', 'pix', 'cartao_credito', 'cartao_debito', 'boleto', 'transferencia'];
        if (!in_array($metodoManual, $metodosManuaisPermitidos, true)) {
            throw new \InvalidArgumentException('Método de pagamento inválido.');
        }

        $statusAprovado = ['succeeded', 'approved', 'paid', 'completed', 'complete', 'confirmed', 'concluido'];
        $valorTotalReserva = (float) ($reserva->valor_total_final ?? $reserva->valor_total_com_desconto ?? $reserva->valor_total_tarifa_base ?? 0);
        $totalJaPago = (float) $reserva->transacoes
            ->whereIn('status', $statusAprovado)
            ->sum('valor');
        $saldoPendente = max(0, $valorTotalReserva - $totalJaPago);

        if ($transacaoId) {
            $transacao = Transacao::where('reserva_id', $reserva->id)->where('id', $transacaoId)->firstOrFail();
            if ($transacao->confirmado_em) {
                throw new \RuntimeException('Pagamento já confirmado.');
            }
            $transacao->update([
                'status' => 'approved',
                'confirmado_em' => now(),
                'confirmado_por_user_id' => Auth::id(),
            ]);
            $valorLog = $transacao->valor;
        } else {
            if ($valor <= 0) {
                throw new \InvalidArgumentException('Informe um valor válido para o pagamento.');
            }
            if ($valor > $saldoPendente) {
                throw new \RuntimeException('Valor informado (R$ ' . number_format($valor, 2, ',', '.') . ') ultrapassa o saldo pendente de R$ ' . number_format($saldoPendente, 2, ',', '.') . '.');
            }
            $transacao = Transacao::create([
                'user_id' => $reserva->user_id,
                'pago_por_user_id' => Auth::id(),
                'reserva_id' => $reserva->id,
                'valor' => $valor,
                'moeda' => 'BRL',
                'metodo_pagamento' => $metodoManual,
                'status' => 'approved',
                'confirmado_em' => now(),
                'confirmado_por_user_id' => Auth::id(),
                'info_pagamento' => ['metodo' => 'dinheiro', 'registrado_por' => Auth::user()?->name ?? 'Sistema'],
            ]);
            $valorLog = $valor;
        }

        $this->reservaService->adicionaLog($reserva->id, Auth::id(), json_encode([
            'acao' => 'Pagamento em dinheiro confirmado',
            'transacao_id' => $transacao->id,
            'valor' => $valorLog,
        ]), 'success');

        return $transacao;
    }
}
