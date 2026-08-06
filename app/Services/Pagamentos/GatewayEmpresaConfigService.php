<?php

namespace App\Services\Pagamentos;

use App\Models\Empresa;
use App\Support\EmpresaContext;
use Illuminate\Support\Facades\Auth;

class GatewayEmpresaConfigService
{
    public function empresaAtual(): ?Empresa
    {
        $empresaId = $this->empresaIdAtual();
        if ($empresaId === null) {
            return null;
        }

        return Empresa::withoutGlobalScopes()->find($empresaId);
    }

    public function empresaPorId(int $empresaId): ?Empresa
    {
        return Empresa::withoutGlobalScopes()->find($empresaId);
    }

    public function gateway(Empresa $empresa): string
    {
        $gateway = strtolower((string) ($empresa->gateway_pagamento ?: 'stripe'));

        if ($gateway === 'nenhum' || $gateway === 'none' || $gateway === 'sem_gateway') {
            return '';
        }

        return $gateway === 'mercadolivre' ? 'mercado_livre' : $gateway;
    }

    public function moeda(Empresa $empresa): string
    {
        return strtoupper((string) ($empresa->moeda_pagamento ?: 'BRL'));
    }

    public function credenciais(Empresa $empresa, string $gateway): array
    {
        $gateway = strtolower($gateway);

        return match ($gateway) {
            'stripe' => [
                'stripe_public_key' => $empresa->stripe_public_key,
                'stripe_secret' => $empresa->stripe_secret,
            ],
            'mercado_livre', 'mercadolivre' => [
                'mercado_livre_public_key' => $empresa->mercado_livre_public_key,
                'mercado_livre_access_token' => $empresa->mercado_livre_access_token,
            ],
            'paypal' => [
                'paypal_client_id' => $empresa->paypal_client_id,
                'paypal_client_secret' => $empresa->paypal_client_secret,
                'paypal_mode' => $empresa->paypal_mode ?: 'sandbox',
            ],
            default => [],
        };
    }

    public function empresaIdAtual(): ?int
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        if (!$user->isAdministrador()) {
            return $user->empresa_id ? (int) $user->empresa_id : null;
        }

        $empresaContexto = app(EmpresaContext::class)->empresaId();

        return $empresaContexto !== null ? (int) $empresaContexto : null;
    }
}
