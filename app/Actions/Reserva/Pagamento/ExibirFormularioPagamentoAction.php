<?php

namespace App\Actions\Reserva\Pagamento;

use App\Enums\StatusReservaEnum;
use App\Models\Reserva;
use App\Services\ClienteStripeService;
use App\Services\Pagamentos\GatewayEmpresaConfigService;
use App\Services\ReservaService;
use App\Services\UsuarioService;

class ExibirFormularioPagamentoAction
{
    public function __construct(
        private ReservaService $reservaService,
        private GatewayEmpresaConfigService $gatewayEmpresaConfigService,
        private ClienteStripeService $clienteStripeService,
        private UsuarioService $userService,
    ) {}

    public function handle(Reserva $reserva): array
    {
        $taxaColeta  = (float) ($reserva->taxa_coleta  ?? 0);
        $taxaEntrega = (float) ($reserva->taxa_entrega ?? 0);

        if ($taxaColeta == 0 && $reserva->estacaoRetirada && $reserva->data_inicio) {
            $av = $this->reservaService->avaliarFuncionamentoEstacaoPublico(
                (int) $reserva->estacao_retirada_id, $reserva->data_inicio, 'retirada'
            );
            if (($av['taxa_extra'] ?? false) && ($av['ok'] ?? false)) {
                $taxaColeta = (float) ($reserva->estacaoRetirada->valor_taxa_coleta ?? 0);
            }
        }

        if ($taxaEntrega == 0 && $reserva->estacaoDevolucao && $reserva->data_fim) {
            $av = $this->reservaService->avaliarFuncionamentoEstacaoPublico(
                (int) $reserva->estacao_devolucao_id, $reserva->data_fim, 'devolucao'
            );
            if (($av['taxa_extra'] ?? false) && ($av['ok'] ?? false)) {
                $taxaEntrega = (float) ($reserva->estacaoDevolucao->valor_taxa_entrega ?? 0);
            }
        }

        if ((float) ($reserva->percentual_desconto ?? 0) == 0 && $reserva->cupom_desconto_id && $reserva->cupomDesconto) {
            $reserva->percentual_desconto = (float) ($reserva->cupomDesconto->valor ?? 0);
        }

        $diasReserva = (int) ($reserva->quantidade_dias ?? 0);
        $tarifaBase  = (float) ($reserva->tarifa_base ?? 0);
        $valorTarifaBase = $tarifaBase * $diasReserva;
        $valorOpcionais  = (float) ($reserva->valor_opcionais ?? 0);

        if ($valorOpcionais <= 0 && $reserva->opcionais?->isNotEmpty()) {
            $valorOpcionais = (float) $reserva->opcionais->sum(
                fn ($opcional) => (float) ($opcional->pivot->preco_total ?? 0)
            );
        }

        $subtotal       = $valorTarifaBase + $valorOpcionais + $taxaColeta + $taxaEntrega;
        $valorDesconto  = $subtotal * ((float) ($reserva->percentual_desconto ?? 0) / 100);
        $valorTotalAtual = max(0, $subtotal - $valorDesconto);

        $statusPagamentoAprovado = ['succeeded', 'approved', 'paid', 'completed', 'complete', 'confirmed', 'concluido'];

        $totalPagoAprovado = (float) ($reserva->transacoes ?? collect())
            ->filter(fn ($item) => (float) ($item->valor ?? 0) > 0
                && in_array(strtolower((string) ($item->status ?? '')), $statusPagamentoAprovado, true))
            ->sum('valor');

        $saldoPendente = max(0, $valorTotalAtual - $totalPagoAprovado);
        $valorCobranca = $saldoPendente;

        $empresa         = $this->gatewayEmpresaConfigService->empresaPorId((int) $reserva->empresa_id);
        $gatewayPadrao   = $empresa ? $this->gatewayEmpresaConfigService->gateway($empresa) : null;
        $moedaPadrao     = $empresa ? $this->gatewayEmpresaConfigService->moeda($empresa) : null;

        $gatewayNome = [
            'stripe'       => 'Stripe',
            'mercado_livre' => 'Mercado Livre',
            'paypal'       => 'PayPal',
        ][$gatewayPadrao] ?? strtoupper((string) $gatewayPadrao);

        $user = $reserva->user_id ? $this->userService->getById($reserva->user_id) : null;
        $metodosPagamento = [];
        $stripePublicKey  = null;

        if ($gatewayPadrao === 'stripe' && $user && !empty($user->stripe_id) && $empresa) {
            $stripePublicKey = (string) ($empresa->stripe_public_key ?? config('services.stripe.key'));
            $stripeSecret    = (string) ($empresa->stripe_secret ?? config('services.stripe.secret'));
            if ($stripeSecret !== '') {
                try {
                    $metodosPagamento = $this->clienteStripeService->getPaymentMethods(
                        $user->stripe_id, $stripeSecret
                    );
                } catch (\Throwable $e) {
                    $metodosPagamento = [];
                }
            }
        }

        return [
            'reserva'            => $reserva,
            'metodosPagamento'   => $metodosPagamento,
            'gatewayPadrao'      => $gatewayPadrao,
            'gatewayNome'        => $gatewayNome,
            'moedaPadrao'        => $moedaPadrao,
            'stripePublicKey'    => $stripePublicKey,
            'taxaColeta'         => $taxaColeta,
            'taxaEntrega'        => $taxaEntrega,
            'totalPagoAprovado'  => $totalPagoAprovado,
            'saldoPendente'      => $saldoPendente,
            'valorCobranca'      => $valorCobranca,
        ];
    }
}
