<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ia\SalvarIaGlobalRequest;
use App\Models\IaConfigGlobal;
use App\Services\Ia\IaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Configuração GLOBAL de IA (superadmin): provedor/chave/modelo padrão
 * da plataforma, toggle do assistente e transcrição de áudio.
 * Fallback para empresas sem configuração própria.
 */
class IaGlobalController extends Controller
{
    public function edit(): View
    {
        $config = IaConfigGlobal::atual();

        return view('ia.global', [
            'titulo' => 'Provedor de IA',
            'config' => $config,
            'provedores' => IaService::provedores(),
            'transcricaoProvedores' => IaConfigGlobal::TRANSCRICAO_PROVEDORES,
            'configurado' => $config->api_key || config('services.'.($config->provedor ?: 'deepseek').'.key'),
            'usoMesGlobal' => DB::table('ia_usos')
                ->where('created_at', '>=', now()->startOfMonth())
                ->selectRaw('count(*) as chamadas, coalesce(sum(tokens_entrada + tokens_saida),0) as tokens, coalesce(sum(custo_estimado),0) as custo')
                ->first(),
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Configurações'],
                ['name' => 'Integração com IA', 'active' => true],
            ],
        ]);
    }

    public function update(SalvarIaGlobalRequest $request): RedirectResponse
    {
        $dados = $request->validated();
        $config = IaConfigGlobal::atual();

        $config->fill([
            'provedor' => $dados['provedor'],
            'modelo' => trim((string) ($dados['modelo'] ?? '')) ?: null,
            'assistente_ativo' => ! empty($dados['assistente_ativo']) ? 'S' : 'N',
            'transcricao_provedor' => $dados['transcricao_provedor'] ?? '',
            'transcricao_modelo' => trim((string) ($dados['transcricao_modelo'] ?? '')) ?: null,
        ]);

        if (! empty($dados['remover_api_key'])) {
            $config->api_key = null;
        } elseif (trim((string) ($dados['api_key'] ?? '')) !== '') {
            $config->api_key = trim((string) $dados['api_key']);
        }

        if (! empty($dados['remover_transcricao_key'])) {
            $config->transcricao_key = null;
        } elseif (trim((string) ($dados['transcricao_key'] ?? '')) !== '') {
            $config->transcricao_key = trim((string) $dados['transcricao_key']);
        }

        $config->save();

        return redirect()->route('ia-global.edit')
            ->with('success', 'Configuração de IA salva.');
    }
}
