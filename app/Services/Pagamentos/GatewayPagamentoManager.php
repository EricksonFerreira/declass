<?php

namespace App\Services\Pagamentos;

use App\Contracts\GatewayPagamento;
use App\Models\Empresa;
use InvalidArgumentException;
use RuntimeException;

/**
 * Ponto unico de resolucao do gateway da empresa.
 * Novos provedores: implementar GatewayPagamento e registrar aqui.
 */
class GatewayPagamentoManager
{
    public function for(Empresa $empresa): GatewayPagamento
    {
        $driver = $empresa->gateway_pagamento;

        if (! $driver || ! $empresa->gateway_api_key) {
            throw new RuntimeException('Nenhum gateway de pagamento configurado para esta empresa.');
        }

        return match ($driver) {
            'asaas' => new AsaasGateway($empresa->gateway_api_key, $empresa->gateway_ambiente ?? 'sandbox'),
            default => throw new InvalidArgumentException("Gateway '{$driver}' não suportado."),
        };
    }

    public function configurado(?Empresa $empresa): bool
    {
        return $empresa && $empresa->gateway_pagamento && $empresa->gateway_api_key;
    }
}
