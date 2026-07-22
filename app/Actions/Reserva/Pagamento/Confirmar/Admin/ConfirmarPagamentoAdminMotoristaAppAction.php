<?php

namespace App\Actions\Reserva\Pagamento\Confirmar\Admin;

use App\Actions\Financeiro\RegistrarReceitaPagamentoReservaAction;
use App\Models\ReservaMotoristaAppPagamento;
use App\Services\Pagamentos\GatewayEmpresaConfigService;
use App\Services\Pagamentos\GerenciadorDePagamentos;
use App\Services\ReservaService;
use Illuminate\Http\Request;

class ConfirmarPagamentoAdminMotoristaAppAction
{
    public function __construct(
        private GatewayEmpresaConfigService $gatewayEmpresaConfigService,
        private GerenciadorDePagamentos $gerenciadorDePagamentos,
        private ReservaService $reservaService,
        private RegistrarReceitaPagamentoReservaAction $registrarReceitaPagamentoReservaAction,
    ) {}

    public function handle(
        ReservaMotoristaApp $reservaMotoristaApp,
        Request $request
    ): mixed {
        $reservaMotoristaApp->loadMissing([
            'codigoReserva',
            'empresa',
            'user',
            'planoMotoristaApp',
        ]);

        $validated = $request->validate([
            'metodo_pagamento' => ['required', 'string', 'max:30'],
            'referencia_pagamento' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['nullable', 'string', 'max:120'],
            'installments' => ['nullable', 'integer', 'min:1', 'max:12'],
            'valor_pagamento' => ['required', 'numeric', 'min:0.01'],
        ]);

        $valorTotalReserva = $this->calcularValorTotalReserva(
            $reservaMotoristaApp
        );

        $totalPago = (float) $reservaMotoristaApp->pagamentos()
            ->where('status', 'pago')
            ->sum('valor_total');

        $saldoAtual = max(
            0,
            round($valorTotalReserva - $totalPago, 2)
        );

        $valorCobrado = round(
            (float) $validated['valor_pagamento'],
            2
        );

        if ($saldoAtual <= 0) {
            return back()->with(
                'warning',
                'A reserva já se encontra totalmente quitada.'
            );
        }

        if ($valorCobrado > $saldoAtual) {
            return back()
                ->withInput()
                ->withErrors([
                    'valor_pagamento' => 'O valor informado ultrapassa o saldo de R$ '
                        .number_format($saldoAtual, 2, ',', '.').'.',
                ]);
        }

        /*
         * Processa cartão/Stripe antes de registrar como pago.
         */

        $pagamento = ReservaMotoristaAppPagamento::create([
            'reserva_motorista_app_id' => $reservaMotoristaApp->id,
            'empresa_id' => $reservaMotoristaApp->empresa_id,
            'user_id' => $reservaMotoristaApp->user_id,
            'periodos' => 1,
            'periodo_unidade' => 'pagamento',
            'valor_unitario' => $valorCobrado,
            'valor_total' => $valorCobrado,
            'data_inicio_prevista' => $reservaMotoristaApp->data_inicio,
            'data_fim_prevista' => $reservaMotoristaApp->data_fim,
            'metodo_pagamento' => $validated['metodo_pagamento'],
            'referencia_pagamento' => $validated['referencia_pagamento'] ?? null,
            'status' => 'pago',
            'pago_em' => now(),
        ]);

        $novoTotalPago = round($totalPago + $valorCobrado, 2);
        $novoSaldo = max(
            0,
            round($valorTotalReserva - $novoTotalPago, 2)
        );

        $reservaMotoristaApp->update([
            'status_pagamento' => $novoSaldo <= 0.009
                ? 'pago'
                : 'pendente',

            'pagamento_efetuado' => $novoSaldo <= 0.009,
        ]);

        return redirect()
            ->route(
                'reserva.show',
                $reservaMotoristaApp->codigoReserva?->codigo
            )
            ->with(
                'success',
                $novoSaldo > 0
                    ? 'Pagamento registrado. Saldo restante: R$ '
                        .number_format($novoSaldo, 2, ',', '.')
                    : 'Pagamento registrado. Reserva totalmente quitada.'
            );
    }
}
