<?php

namespace App\Services\Pagamentos;

class GerenciadorDePagamentos
{
    public function resolver(string $gateway): IPagamento
    {
        $gateways = [
            'paypal' => PayPalService::class,
            'mercadolivre' => MercadoLivreService::class,
        ];

        if (!isset($gateways[$gateway])) {
            throw new \Exception("Gateway de pagamento não encontrado");
        }

        return app($gateways[$gateway]);
    }
}
