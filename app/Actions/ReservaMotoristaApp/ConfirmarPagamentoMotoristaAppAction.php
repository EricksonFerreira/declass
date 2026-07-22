<?php

namespace App\Actions\ReservaMotoristaApp;

use App\Actions\Financeiro\RegistrarReceitaPagamentoReservaAction;
use App\Models\ReservaMotoristaAppPagamento;
use App\Services\Pagamentos\GatewayEmpresaConfigService;
use App\Services\Pagamentos\GerenciadorDePagamentos;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ConfirmarPagamentoMotoristaAppAction
{
    public function __construct(
        private GatewayEmpresaConfigService $gatewayEmpresaConfigService,
        private GerenciadorDePagamentos $gerenciadorDePagamentos,
        private RegistrarReceitaPagamentoReservaAction $registrarReceitaPagamentoReservaAction,
    ) {}

    public function handle(
        ReservaMotoristaAppPagamento $pagamento,
        array $validated,
        callable $valorUnitarioCalc
    ): ReservaMotoristaAppPagamento {
        if (strtolower((string) $pagamento->status) === 'pago') {
            return $pagamento;
        }

        $reserva = $pagamento->reservaMotoristaApp;
        if (!$reserva) {
            throw new \RuntimeException('Reserva nao encontrada para este pagamento.');
        }

        $fimConfirmado = Carbon::parse($pagamento->data_fim_prevista ?? now());
        $inicioConfirmado = Carbon::parse($pagamento->data_inicio_prevista ?? $reserva->data_inicio ?? now());
        $diasAtualizados = max(1, $inicioConfirmado->diffInDays($fimConfirmado));
        $periodos = max(1, (int) ($pagamento->periodos ?? 1));
        $valorOpcionaisReserva = (float) ($reserva->valor_opcionais ?? 0);
        $valorUnitario = (float) $pagamento->valor_unitario > 0
            ? (float) $pagamento->valor_unitario
            : $valorUnitarioCalc($reserva);
        $valorBaseCalculado = $valorUnitario * $periodos;
        $valorTotal = (float) ($pagamento->valor_total > $valorBaseCalculado
            ? $pagamento->valor_total
            : round($valorBaseCalculado + $valorOpcionaisReserva, 2));

        if ($valorTotal <= 0 || $valorUnitario <= 0) {
            throw new \RuntimeException('Nao foi possivel identificar o valor do plano para este pagamento.');
        }

        $valorCobrado = (float) ($validated['valor_pagamento'] ?? $valorTotal);
        if ($valorCobrado <= 0 || $valorCobrado > $valorTotal) {
            $valorCobrado = $valorTotal;
        }

        $metodoPagamento = $validated['metodo_pagamento'] ?? 'cartao_credito';
        $gatewayTransactionId = null;
        $gatewayResultado = null;

        if (!empty($validated['payment_method'])) {
            $empresa = $pagamento->empresa ?? $reserva->empresa ?? $this->gatewayEmpresaConfigService->empresaPorId((int) $reserva->empresa_id);
            if (!$empresa) {
                throw new \RuntimeException('Empresa da reserva nao encontrada para configuracao de pagamento.');
            }

            $gateway = $this->gatewayEmpresaConfigService->gateway($empresa);
            if ($gateway !== 'stripe') {
                throw new \InvalidArgumentException('Pagamento por cartao nesta tela requer Stripe como gateway.');
            }

            $currency = $this->gatewayEmpresaConfigService->moeda($empresa);
            $credenciais = $this->gatewayEmpresaConfigService->credenciais($empresa, $gateway);

            $payload = array_merge([
                'reserva_motorista_app_id' => $reserva->id,
                'valor' => $valorCobrado,
                'currency' => $currency,
                'payment_method' => (string) ($validated['payment_method'] ?? ''),
                'customer_id' => (string) ($reserva->user?->stripe_id ?? ''),
                'descricao' => 'Pagamento da reserva motorista APP #' . $reserva->id,
                'payer_email' => (string) ($reserva->user?->email ?? ''),
                'installments' => (int) ($validated['installments'] ?? 1),
            ], $credenciais);

            try {
                $gatewayResultado = $this->gerenciadorDePagamentos
                    ->resolver($gateway)
                    ->cobrarPagamento($payload);
            } catch (\Throwable $e) {
                Log::error('Falha ao processar pagamento motorista APP', [
                    'pagamento_id' => (int) $pagamento->id,
                    'reserva_motorista_app_id' => (int) $reserva->id,
                    'message' => $e->getMessage(),
                ]);
                throw new \RuntimeException('Falha ao processar o pagamento no cartao. Tente novamente.');
            }

            if (($gatewayResultado['status'] ?? 'error') !== 'success') {
                throw new \RuntimeException($gatewayResultado['message'] ?? 'Pagamento nao efetuado.');
            }

            $metodoPagamento = 'cartao_credito';
            $gatewayTransactionId = $gatewayResultado['transaction_id'] ?? null;
        }

        $saldoRestante = round($valorTotal - $valorCobrado, 2);

        if ($saldoRestante > 0) {
            $pagamento->update([
                'status' => 'pago',
                'valor_unitario' => $valorUnitario,
                'valor_total' => $valorCobrado,
                'metodo_pagamento' => $metodoPagamento,
                'pago_em' => now(),
                'referencia_pagamento' => $validated['referencia_pagamento'] ?? $gatewayTransactionId,
                'observacoes' => trim(($pagamento->observacoes ?? '') . ' (Pagamento parcial de R$ ' . number_format($valorCobrado, 2, ',', '.') . ')'),
                'metadados' => array_merge((array) ($pagamento->metadados ?? []), [
                    'gateway_transaction_id' => $gatewayTransactionId,
                    'gateway_resultado' => $gatewayResultado,
                    'pagamento_parcial' => true,
                    'valor_original_ciclo' => $valorTotal,
                ]),
            ]);

            ReservaMotoristaAppPagamento::create([
                'reserva_motorista_app_id' => (int) $reserva->id,
                'empresa_id' => $reserva->empresa_id,
                'user_id' => (int) $reserva->user_id,
                'periodos' => 1,
                'periodo_unidade' => $pagamento->periodo_unidade ?: 'ciclo',
                'valor_unitario' => $saldoRestante,
                'valor_total' => $saldoRestante,
                'data_inicio_prevista' => $pagamento->data_inicio_prevista,
                'data_fim_prevista' => $pagamento->data_fim_prevista,
                'status' => 'pendente',
                'observacoes' => 'Saldo remanescente de ciclo (R$ ' . number_format($saldoRestante, 2, ',', '.') . ' pendente de R$ ' . number_format($valorTotal, 2, ',', '.') . ').',
                'metadados' => [
                    'tipo' => 'saldo_remanescente',
                    'pagamento_pai_id' => $pagamento->id,
                ],
            ]);

            $reserva->fill([
                'status_pagamento' => 'pendente',
                'pagamento_efetuado' => false,
            ])->save();

            $this->registrarReceitaPagamentoReservaAction->handle(
                $reserva,
                $valorCobrado,
                $metodoPagamento,
                now(),
                (string) ($pagamento->referencia_pagamento ?: $gatewayTransactionId ?: 'pagamento_motorista_app_'.$pagamento->id),
                (int) ($pagamento->user_id ?? $reserva->user_id),
                'pagamento_motorista_app_'.$pagamento->id
            );

            return $pagamento;
        }

        $pagamento->update([
            'status' => 'pago',
            'valor_unitario' => $valorUnitario,
            'valor_total' => $valorTotal,
            'metodo_pagamento' => $metodoPagamento,
            'pago_em' => now(),
            'referencia_pagamento' => $validated['referencia_pagamento'] ?? $gatewayTransactionId,
            'metadados' => array_merge((array) ($pagamento->metadados ?? []), [
                'gateway_transaction_id' => $gatewayTransactionId,
                'gateway_resultado' => $gatewayResultado,
            ]),
        ]);

        $contratoAssinado = !empty(data_get($reserva->contrato_snapshot_json, 'assinatura'));
        $temOutrosPendentes = $reserva->pagamentos()->where('id', '!=', $pagamento->id)->where('status', 'pendente')->exists();

        $reserva->fill([
            'data_fim' => $fimConfirmado,
            'quantidade_dias' => $diasAtualizados,
            'valor_plano' => $valorUnitario,
            'valor_total_final' => $valorTotal,
            'status' => $contratoAssinado ? 'ativa' : 'confirmada',
            'status_pagamento' => $temOutrosPendentes ? 'pendente' : 'pago',
            'pagamento_efetuado' => !$temOutrosPendentes,
        ])->save();

        $this->registrarReceitaPagamentoReservaAction->handle(
            $reserva,
            $valorTotal,
            $metodoPagamento,
            now(),
            (string) ($pagamento->referencia_pagamento ?: $gatewayTransactionId ?: 'pagamento_motorista_app_'.$pagamento->id),
            (int) ($pagamento->user_id ?? $reserva->user_id),
            'pagamento_motorista_app_'.$pagamento->id
        );

        return $pagamento;
    }
}
