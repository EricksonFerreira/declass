<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Configura o gateway de pagamento da empresa (Asaas hoje,
 * extensivel a outros provedores via GatewayPagamento).
 */
class EmpresaIntegracaoController extends Controller
{
    public function edit()
    {
        $empresa = Empresa::withoutGlobalScopes()->findOrFail(tenant_id());
        $webhookUrl = route('webhook.asaas') . ($empresa->gateway_webhook_token ? '?token='.$empresa->gateway_webhook_token : '');

        return view('empresa.integracoes', compact('empresa', 'webhookUrl'));
    }

    public function update(Request $request)
    {
        $dados = $request->validate([
            'gateway_pagamento' => 'nullable|in:asaas',
            'gateway_api_key' => 'nullable|string|max:2000',
            'gateway_ambiente' => 'required|in:sandbox,producao',
            'regerar_webhook' => 'nullable|boolean',
        ]);

        $empresa = Empresa::withoutGlobalScopes()->findOrFail(tenant_id());
        $empresa->update([
            'gateway_pagamento' => $dados['gateway_pagamento'],
            'gateway_ambiente' => $dados['gateway_ambiente'],
            'gateway_api_key' => $dados['gateway_api_key'] !== null
                ? $dados['gateway_api_key']
                : $empresa->gateway_api_key,
            'gateway_webhook_token' => ($dados['regerar_webhook'] ?? false) || ! $empresa->gateway_webhook_token
                ? Str::random(48)
                : $empresa->gateway_webhook_token,
        ]);

        return back()->with('success', 'Integração de pagamento salva. Cadastre a URL do webhook no painel Asaas.');
    }
}
