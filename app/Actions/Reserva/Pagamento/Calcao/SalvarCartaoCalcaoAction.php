<?php

namespace App\Actions\Reserva\Pagamento\Calcao;

use App\Models\Reserva;
use App\Services\ClienteStripeService;
use App\Services\Pagamentos\GatewayEmpresaConfigService;
use App\Services\UsuarioService;

class SalvarCartaoCalcaoAction
{
    public function __construct(
        private ClienteStripeService $clienteStripeService,
        private GatewayEmpresaConfigService $gatewayEmpresaConfigService,
        private UsuarioService $userService,
    ) {}

    public function handle(Reserva $reserva, string $paymentMethodId): array
    {
        $empresa = $this->gatewayEmpresaConfigService->empresaAtual();
        if (!$empresa || !$empresa->stripe_secret) {
            throw new \RuntimeException('Stripe não configurado.');
        }

        $user = $reserva->usuario;
        if (!$user) {
            throw new \RuntimeException('Cliente não encontrado.');
        }

        $stripeId = $this->clienteStripeService->garantirClienteValido(
            $user->stripe_id ?? null,
            $user->email ?? '',
            $user->nome ?? 'Cliente',
            $empresa->stripe_secret
        );

        if (empty($user->stripe_id) || $user->stripe_id !== $stripeId) {
            $this->userService->updateUser($user->id, ['stripe_id' => $stripeId]);
        }

        $pm = $this->clienteStripeService->attachPaymentMethod(
            $paymentMethodId,
            $stripeId,
            $empresa->stripe_secret
        );

        return [
            'id'        => $pm->id,
            'brand'     => $pm->card->brand ?? '',
            'last4'     => $pm->card->last4 ?? '',
            'exp_month' => $pm->card->exp_month ?? '',
            'exp_year'  => $pm->card->exp_year ?? '',
        ];
    }
}
