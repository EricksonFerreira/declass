<?php

namespace App\Services\Pagamentos;

use Illuminate\Support\Facades\Auth;

class GerenciadorDePagamentos
{
    /**
     * @return array<string, class-string<IPagamento>>
     */
    public function gateways(): array
    {
        return [
            'stripe' => StripeGatewayService::class,
            'paypal' => PayPalService::class,
            'mercado_livre' => MercadoLivreService::class,
            'mercadolivre' => MercadoLivreService::class,
        ];
    }

    /**
     * @return array<int, string>
     */
    public function nomesGatewaysDisponiveis(): array
    {
        return array_values(array_unique(array_keys($this->gateways())));
    }

    public function resolver(string $gateway): IPagamento
    {
        $normalized = $this->normalizarGateway($gateway);
        $gateways = $this->gateways();

        if (!isset($gateways[$normalized])) {
            throw new \InvalidArgumentException('Gateway de pagamento nao encontrado');
        }

        return app($gateways[$normalized]);
    }

    public function resolverGatewayPadraoDaEmpresa(): string
    {
        $gateway = Auth::user()?->empresa?->gateway_pagamento;
        if (is_string($gateway) && $gateway !== '') {
            return $this->normalizarGateway($gateway);
        }

        $fallback = (string) config('services.payment.default_gateway', 'stripe');

        return $this->normalizarGateway($fallback);
    }

    public function normalizarGateway(string $gateway): string
    {
        $value = strtolower(trim($gateway));

        if ($value === 'mercadolivre') {
            return 'mercado_livre';
        }

        return $value;
    }
}
