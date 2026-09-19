<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ia\SalvarConfiguracaoIaRequest;
use App\Models\EmpresaIaConfig;
use App\Services\Ia\IaService;
use App\Support\EmpresaContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Integração com IA por empresa: provedor padrão, chaves, modelo,
 * limite de tokens, anonização LGPD e instruções extras.
 */
class ConfiguracaoIaController extends Controller
{
    public function edit(): View
    {
        $empresaId = $this->empresaId();

        return view('configuracoes.ia', [
            'titulo' => 'Integração com IA',
            'config' => app(IaService::class)->configEmpresa($empresaId),
            'provedores' => IaService::provedores(),
            'tamanhos' => EmpresaIaConfig::TAMANHOS_RESUMO,
            'usoMes' => app(IaService::class)->usoMes($empresaId),
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Gerenciar'],
                ['name' => 'Integração com IA', 'active' => true],
            ],
        ]);
    }

    public function update(SalvarConfiguracaoIaRequest $request): RedirectResponse
    {
        $empresaId = $this->empresaId();
        $dados = $request->validated();

        $config = EmpresaIaConfig::withoutGlobalScope('empresa')
            ->firstOrNew(['empresa_id' => $empresaId]);

        $config->fill([
            'provedor_padrao' => $dados['provedor_padrao'],
            'tokens_limite' => (int) $dados['tokens_limite'],
            'anonimizar' => ! empty($dados['anonimizar']) ? 'S' : 'N',
            'tamanho_resumo' => $dados['tamanho_resumo'],
            'instrucoes_extras' => trim((string) ($dados['instrucoes_extras'] ?? '')) ?: null,
            'deepseek_model' => trim((string) ($dados['deepseek_model'] ?? '')) ?: null,
            'openai_model' => trim((string) ($dados['openai_model'] ?? '')) ?: null,
        ]);

        // Chave em branco mantém a atual; checkbox "remover" zera.
        foreach (['deepseek', 'openai'] as $provedor) {
            if (! empty($dados["remover_{$provedor}_key"])) {
                $config->{$provedor.'_key'} = null;
            } elseif (trim((string) ($dados[$provedor.'_key'] ?? '')) !== '') {
                $config->{$provedor.'_key'} = trim((string) $dados[$provedor.'_key']);
            }
        }

        $config->usualt = $request->user()?->id;
        if (! $config->exists) {
            $config->usuinc = $request->user()?->id;
            $config->datainc = now();
        }
        $config->dataalt = now();
        $config->save();

        return redirect()->route('configuracao-ia.edit')
            ->with('success', 'Configurações de IA salvas.');
    }

    private function empresaId(): int
    {
        return (int) (app(EmpresaContext::class)->empresaId() ?? auth()->user()->empresa_id);
    }
}
