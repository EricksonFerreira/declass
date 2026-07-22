<?php

namespace App\Actions\Reserva\Pagamento\Confirmar\Admin;

use App\Actions\Financeiro\RegistrarReceitaPagamentoReservaAction;
use App\Models\ReservaMotoristaAppPagamento;
use App\Services\Pagamentos\GatewayEmpresaConfigService;
use App\Services\Pagamentos\GerenciadorDePagamentos;
use App\Services\ReservaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ConfirmarPagamentoAdminMotoristaAppAction
{
    public function __construct(
        private GatewayEmpresaConfigService $gatewayEmpresaConfigService,
        private GerenciadorDePagamentos $gerenciadorDePagamentos,
        private ReservaService $reservaService,
        private RegistrarReceitaPagamentoReservaAction $registrarReceitaPagamentoReservaAction,
    ) {}

    public function handle(ReservaMotoristaAppPagamento $pagamento, Request $request): mixed
    {
        $reservaMotoristaApp = $pagamento->reservaMotoristaApp;

        if (! $reservaMotoristaApp) {
            throw new \RuntimeException('Reserva não encontrada para este pagamento.');
        }

        $reservaMotoristaApp->loadMissing(['codigoReserva', 'empresa', 'user', 'planoMotoristaApp']);

        $codigoReserva = $reservaMotoristaApp->codigoReserva?->codigo ?? ('MAP-'.str_pad((string) $reservaMotoristaApp->id, 6, '0', STR_PAD_LEFT));

        if (strtolower((string) ($pagamento->status ?? '')) === 'pago') {
            return redirect()
                ->route('reserva.show', $codigoReserva)
                ->with('success', 'Este pagamento já se encontra quitado.');
        }

        $validated = $request->validate([
            'metodo_pagamento' => ['nullable', 'string', 'max:30'],
            'referencia_pagamento' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['nullable', 'string', 'max:120'],
            'installments' => ['nullable', 'integer', 'min:1', 'max:12'],
            'valor_pagamento' => ['nullable', 'numeric', 'min:0.01'],
        ]);

        try {
            $fimConfirmado = Carbon::parse($pagamento->data_fim_prevista ?? now());
            $inicioConfirmado = Carbon::parse($pagamento->data_inicio_prevista ?? $reservaMotoristaApp->data_inicio ?? now());
            $diasAtualizados = max(1, $inicioConfirmado->diffInDays($fimConfirmado));
            $periodos = max(1, (int) ($pagamento->periodos ?? 1));
            $valorOpcionaisReserva = (float) ($reservaMotoristaApp->valor_opcionais ?? 0);
            $valorUnitarioPlano = (float) ($reservaMotoristaApp->valor_periodo_contrato ?: ($reservaMotoristaApp->valor_plano ?: ($reservaMotoristaApp->planoMotoristaApp?->valor_periodo ?? 0)));
            $valorUnitario = (float) $pagamento->valor_unitario > 0
                ? (float) $pagamento->valor_unitario
                : $valorUnitarioPlano;
            $valorBaseCalculado = $valorUnitario * $periodos;
            $valorTotal = (float) $pagamento->valor_total > 0
                ? (float) $pagamento->valor_total
                : round($valorBaseCalculado + $valorOpcionaisReserva, 2);

            if ($valorTotal <= 0 || $valorUnitario <= 0) {
                throw new \RuntimeException('Não foi possível identificar o valor do plano para este pagamento.');
            }

            $valorCobrado = (float) ($validated['valor_pagamento'] ?? $valorTotal);
            if ($valorCobrado <= 0 || $valorCobrado > $valorTotal) {
                $valorCobrado = $valorTotal;
            }

            $metodoPagamento = $validated['metodo_pagamento'] ?? 'cartao_credito';
            $gatewayTransactionId = null;
            $gatewayResultado = null;

            if (! empty($validated['payment_method'])) {
                $empresa = $pagamento->empresa ?? $reservaMotoristaApp->empresa ?? $this->gatewayEmpresaConfigService->empresaPorId((int) $reservaMotoristaApp->empresa_id);
                if (! $empresa) {
                    throw new \RuntimeException('Empresa da reserva não encontrada para configuração de pagamento.');
                }

                $gateway = $this->gatewayEmpresaConfigService->gateway($empresa);
                if ($gateway !== 'stripe') {
                    throw new \InvalidArgumentException('Pagamento por cartão nesta tela requer Stripe como gateway.');
                }

                $currency = $this->gatewayEmpresaConfigService->moeda($empresa);
                $credenciais = $this->gatewayEmpresaConfigService->credenciais($empresa, $gateway);

                $payload = array_merge([
                    'reserva_motorista_app_id' => $reservaMotoristaApp->id,
                    'valor' => $valorCobrado,
                    'currency' => $currency,
                    'payment_method' => (string) ($validated['payment_method'] ?? ''),
                    'customer_id' => (string) ($reservaMotoristaApp->user?->stripe_id ?? ''),
                    'descricao' => 'Pagamento da reserva motorista APP #' . $reservaMotoristaApp->id,
                    'payer_email' => (string) ($reservaMotoristaApp->user?->email ?? ''),
                    'installments' => (int) ($validated['installments'] ?? 1),
                ], $credenciais);

                try {
                    $gatewayResultado = $this->gerenciadorDePagamentos
                        ->resolver($gateway)
                        ->cobrarPagamento($payload);
                } catch (\Throwable $e) {
                    Log::error('Falha ao processar pagamento motorista APP', [
                        'pagamento_id' => (int) $pagamento->id,
                        'reserva_motorista_app_id' => (int) $reservaMotoristaApp->id,
                        'message' => $e->getMessage(),
                    ]);
                    throw new \RuntimeException('Falha ao processar o pagamento no cartão. Tente novamente.');
                }

                if (($gatewayResultado['status'] ?? 'error') !== 'success') {
                    throw new \RuntimeException($gatewayResultado['message'] ?? 'Pagamento não efetuado.');
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
                    'reserva_motorista_app_id' => (int) $reservaMotoristaApp->id,
                    'empresa_id' => $reservaMotoristaApp->empresa_id,
                    'user_id' => (int) $reservaMotoristaApp->user_id,
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

                $reservaMotoristaApp->fill([
                    'status_pagamento' => 'pendente',
                    'pagamento_efetuado' => false,
                ])->save();

                $this->registrarReceitaPagamentoReservaAction->handle(
                    $reservaMotoristaApp,
                    $valorCobrado,
                    $metodoPagamento,
                    now(),
                    (string) ($pagamento->referencia_pagamento ?: $gatewayTransactionId ?: 'pagamento_motorista_app_'.$pagamento->id),
                    Auth::id(),
                    'pagamento_motorista_app_'.$pagamento->id
                );
            } else {
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

                $contratoAssinado = ! empty(data_get($reservaMotoristaApp->contrato_snapshot_json, 'assinatura'));
                $temOutrosPendentes = $reservaMotoristaApp->pagamentos()->where('id', '!=', $pagamento->id)->where('status', 'pendente')->exists();

                $reservaMotoristaApp->fill([
                    'data_fim' => $fimConfirmado,
                    'quantidade_dias' => $diasAtualizados,
                    'valor_plano' => $valorUnitario,
                    'valor_total_final' => $valorTotal,
                    'status' => $contratoAssinado ? 'ativa' : 'confirmada',
                    'status_pagamento' => $temOutrosPendentes ? 'pendente' : 'pago',
                    'pagamento_efetuado' => ! $temOutrosPendentes,
                ])->save();

                $this->registrarReceitaPagamentoReservaAction->handle(
                    $reservaMotoristaApp,
                    $valorTotal,
                    $metodoPagamento,
                    now(),
                    (string) ($pagamento->referencia_pagamento ?: $gatewayTransactionId ?: 'pagamento_motorista_app_'.$pagamento->id),
                    Auth::id(),
                    'pagamento_motorista_app_'.$pagamento->id
                );
            }

            $logSucesso = json_encode([
                'acao' => 'Pagamento efetuado motorista APP',
                'pagamento_id' => $pagamento->id,
                'reserva_id' => $reservaMotoristaApp->id,
                'valor_cobrado' => $valorCobrado,
                'metodo_pagamento' => $metodoPagamento,
                'usuario_id' => Auth::id(),
                'status' => 'sucesso',
                'data' => date('Y-m-d H:i:s'),
            ]);
            $this->reservaService->adicionaLog((int) $reservaMotoristaApp->id, (int) Auth::id(), $logSucesso, 'Pagamento efetuado', 'info');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $saldoRestante > 0
                        ? 'Pagamento parcial registrado com sucesso. Saldo restante: R$ ' . number_format($saldoRestante, 2, ',', '.')
                        : 'Pagamento confirmado com sucesso.',
                ]);
            }

            if ($saldoRestante > 0) {
                return redirect()
                    ->back()
                    ->with('success', 'Pagamento parcial registrado com sucesso. Saldo pendente: R$ ' . number_format($saldoRestante, 2, ',', '.'));
            }

            return redirect()
                ->route('reserva.show', $codigoReserva)
                ->with('success', 'Pagamento confirmado com sucesso.');
        } catch (\Throwable $e) {
            $logErro = json_encode([
                'acao' => 'Falha ao confirmar pagamento motorista APP',
                'pagamento_id' => $pagamento->id ?? null,
                'reserva_id' => $reservaMotoristaApp->id ?? null,
                'erro' => $e->getMessage(),
                'usuario_id' => Auth::id(),
                'status' => 'erro',
                'data' => date('Y-m-d H:i:s'),
            ]);
            if (isset($reservaMotoristaApp->id)) {
                $this->reservaService->adicionaLog((int) $reservaMotoristaApp->id, (int) Auth::id(), $logErro, 'Falha no pagamento', 'info');
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }
}


