<?php

namespace App\Actions\Reserva\Pagamento;

use App\Actions\Financeiro\RegistrarReceitaPagamentoReservaAction;
use App\Enums\StatusReservaEnum;
use App\Models\Reserva;
use App\Models\Transacao;
use App\Services\NfseService;
use App\Services\Pagamentos\GatewayEmpresaConfigService;
use App\Services\Pagamentos\GerenciadorDePagamentos;
use App\Services\ReservaService;
use App\Services\UsuarioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RealizarPagamentoAction
{
    public function __construct(
        private ReservaService $reservaService,
        private GatewayEmpresaConfigService $gatewayEmpresaConfigService,
        private GerenciadorDePagamentos $gerenciadorDePagamentos,
        private UsuarioService $userService,
        private NfseService $nfseService,
        private RegistrarReceitaPagamentoReservaAction $registrarReceitaPagamentoReservaAction,
    ) {}

    public function handle(Reserva $reserva, Request $request): array
    {
        $taxaColeta  = (float) ($reserva->taxa_coleta  ?? 0);
        $taxaEntrega = (float) ($reserva->taxa_entrega ?? 0);
        if ($taxaColeta == 0 && $reserva->estacaoRetirada && $reserva->data_inicio) {
            $av = $this->reservaService->avaliarFuncionamentoEstacaoPublico((int) $reserva->estacao_retirada_id, $reserva->data_inicio, 'retirada');
            if (($av['taxa_extra'] ?? false) && ($av['ok'] ?? false)) {
                $taxaColeta = (float) ($reserva->estacaoRetirada->valor_taxa_coleta ?? 0);
            }
        }
        if ($taxaEntrega == 0 && $reserva->estacaoDevolucao && $reserva->data_fim) {
            $av = $this->reservaService->avaliarFuncionamentoEstacaoPublico((int) $reserva->estacao_devolucao_id, $reserva->data_fim, 'devolucao');
            if (($av['taxa_extra'] ?? false) && ($av['ok'] ?? false)) {
                $taxaEntrega = (float) ($reserva->estacaoDevolucao->valor_taxa_entrega ?? 0);
            }
        }
        if ((float) ($reserva->percentual_desconto ?? 0) == 0 && $reserva->cupom_desconto_id && $reserva->cupomDesconto) {
            $reserva->percentual_desconto = (float) ($reserva->cupomDesconto->valor ?? 0);
        }

        $diasReserva = (int) ($reserva->quantidade_dias ?? 0);
        $tarifaBase = (float) ($reserva->tarifa_base ?? 0);
        $valorTarifaBase = $tarifaBase * $diasReserva;
        $valorOpcionais = (float) ($reserva->valor_opcionais ?? 0);
        if ($valorOpcionais <= 0 && $reserva->opcionais?->isNotEmpty()) {
            $valorOpcionais = (float) $reserva->opcionais->sum(fn ($opcional) => (float) ($opcional->pivot->preco_total ?? 0));
        }
        $subtotal = $valorTarifaBase + $valorOpcionais + $taxaColeta + $taxaEntrega;
        $valorDesconto = $subtotal * ((float) ($reserva->percentual_desconto ?? 0) / 100);
        $valorTotal = max(0, $subtotal - $valorDesconto);
        $statusPagamentoAprovado = ['succeeded', 'approved', 'paid', 'completed', 'complete', 'confirmed', 'concluido'];
        $totalPagoAprovado = (float) ($reserva->transacoes ?? collect())
            ->filter(fn ($item) => (float) ($item->valor ?? 0) > 0
                && in_array(strtolower((string) ($item->status ?? '')), $statusPagamentoAprovado, true))
            ->sum('valor');
        $valorCobranca = max(0, $valorTotal - $totalPagoAprovado);

        $valorInformado = (float) ($request->input('valor_pagamento', $valorCobranca));
        if ($valorInformado > $valorCobranca) {
            throw new \RuntimeException('Valor informado (R$ ' . number_format($valorInformado, 2, ',', '.') . ') ultrapassa o saldo pendente de R$ ' . number_format($valorCobranca, 2, ',', '.') . '.');
        }
        if ($valorInformado <= 0) {
            throw new \RuntimeException('Informe um valor válido para o pagamento.');
        }
        $valorCobranca = $valorInformado;

        if ($valorCobranca <= 0.009) {
            throw new \RuntimeException('Esta reserva não possui saldo pendente para pagamento.');
        }

        $empresa = $this->gatewayEmpresaConfigService->empresaPorId((int) $reserva->empresa_id);
        if (!$empresa) {
            throw new \RuntimeException('Empresa da reserva não encontrada para configuração de pagamento.');
        }

        $userReserva = $this->userService->getById($reserva->user_id);
        $gateway = $this->gatewayEmpresaConfigService->gateway($empresa);
        $currency = $this->gatewayEmpresaConfigService->moeda($empresa);
        $credenciais = $this->gatewayEmpresaConfigService->credenciais($empresa, $gateway);

        if ($gateway === 'stripe' && empty($request->payment_method)) {
            throw new \InvalidArgumentException('Selecione um método de pagamento para Stripe.');
        }

        $payload = array_merge([
            'reserva_id' => $reserva->id,
            'valor' => $valorCobranca,
            'currency' => $currency,
            'payment_method' => (string) $request->input('payment_method', ''),
            'customer_id' => (string) ($userReserva->stripe_id ?? ''),
            'descricao' => 'Pagamento da reserva #' . $reserva->id,
            'payer_email' => (string) ($userReserva->email ?? ''),
            'installments' => (int) $request->input('installments', 1),
        ], $credenciais);

        $resultado = $this->gerenciadorDePagamentos
            ->resolver($gateway)
            ->cobrarPagamento($payload);

        if (($resultado['status'] ?? 'error') !== 'success') {
            Log::warning('Pagamento nao efetuado para reserva', [
                'reserva_id' => (int) $reserva->id,
                'gateway' => (string) $gateway,
                'status' => (string) ($resultado['status'] ?? 'error'),
                'raw_status' => (string) ($resultado['raw_status'] ?? ''),
            ]);
            throw new \RuntimeException($resultado['message'] ?? 'Pagamento não efetuado');
        }

        DB::table('reservas')->where('id', $reserva->id)->update(['status_reserva_id' => StatusReservaEnum::Confirmada->value, 'pagamento_efetuado' => 'S']);

        $transacao = Transacao::create([
            'user_id' => $reserva->user_id,
            'pago_por_user_id' => Auth::id(),
            'reserva_id' => $reserva->id,
            'valor' => $valorCobranca,
            'moeda' => strtoupper((string) ($resultado['currency'] ?? $currency)),
            'metodo_pagamento' => $gateway,
            'gateway_transaction_id' => $resultado['transaction_id'] ?? null,
            'status' => $resultado['raw_status'] ?? 'success',
            'info_pagamento' => $resultado,
        ]);

        $this->registrarReceitaPagamentoReservaAction->handle(
            $reserva,
            (float) $transacao->valor,
            (string) $transacao->metodo_pagamento,
            now(),
            (string) ($transacao->gateway_transaction_id ?: 'transacao_'.$transacao->id),
            Auth::id(),
            'transacao_'.$transacao->id
        );

        $json = json_encode([
            'acao' => 'Pagamento efetuado',
            'valor_total' => $valorTotal,
            'valor_pago' => $valorCobranca,
            'saldo_anterior' => $valorCobranca,
            'gateway' => $gateway,
            'moeda' => strtoupper((string) ($resultado['currency'] ?? $currency)),
            'usuario_pagador_id' => Auth::id(),
            'customer_id' => $resultado['customer_id'] ?? null,
            'id_pagamento' => $resultado['transaction_id'] ?? null,
            'status' => $resultado['raw_status'] ?? 'success',
            'data_pagamento' => date('Y-m-d H:i:s'),
        ]);
        $this->reservaService->adicionaLog($reserva->id, (int) Auth::id(), $json);

        $nfse = $this->nfseService->emitirParaReserva($reserva);
        $nfseWarning = null;
        if ($nfse === null) {
            $nfseWarning = 'Emissao de NFS-e não configurada para esta empresa.';
        } elseif (($nfse->status ?? '') === 'erro') {
            $nfseWarning = $this->nfseService->mensagemErro($nfse) ?? 'Falha ao emitir NFS-e.';
        }

        return [
            'success' => true,
            'warning' => $nfseWarning,
        ];
    }
}
