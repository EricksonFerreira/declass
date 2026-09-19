<?php

namespace App\Services\Ia;

use App\Models\EmpresaIaConfig;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Cliente de IA multi-provedor (APIs compatíveis com OpenAI Chat
 * Completions). Resolve provedor, chave, modelo e limite de tokens da
 * configuração da empresa — com fallback para as chaves globais do .env.
 * Novos provedores entram incluindo o endpoint em PROVEDORES.
 */
class IaService
{
    private const PROVEDORES = [
        'deepseek' => [
            'nome' => 'DeepSeek',
            'endpoint' => 'https://api.deepseek.com/chat/completions',
            'modelo_padrao' => 'deepseek-chat',
        ],
        'openai' => [
            'nome' => 'OpenAI',
            'endpoint' => 'https://api.openai.com/v1/chat/completions',
            'modelo_padrao' => 'gpt-4o-mini',
        ],
    ];

    public static function provedores(): array
    {
        return collect(self::PROVEDORES)
            ->map(fn ($p, $slug) => $p['nome'])
            ->all();
    }

    public function configEmpresa(int $empresaId): EmpresaIaConfig
    {
        return EmpresaIaConfig::withoutGlobalScope('empresa')
            ->firstOrNew(['empresa_id' => $empresaId]);
    }

    public function configurado(int $empresaId): bool
    {
        $cfg = $this->configEmpresa($empresaId);

        return $cfg->chaveDo($cfg->provedor_padrao ?: 'deepseek') !== '';
    }

    /**
     * @return array{conteudo:string, tokens_entrada:int|null, tokens_saida:int|null, modelo:string, provedor:string}
     */
    public function chatJson(int $empresaId, string $system, string $user): array
    {
        $cfg = $this->configEmpresa($empresaId);
        $provedor = $cfg->provedor_padrao ?: 'deepseek';
        $meta = self::PROVEDORES[$provedor] ?? self::PROVEDORES['deepseek'];

        $key = $cfg->chaveDo($provedor);
        if ($key === '') {
            throw new RuntimeException(
                "{$meta['nome']} não configurado: informe a API Key em Gerenciar → Integração com IA."
            );
        }

        $modelo = $cfg->modeloDo($provedor) ?: $meta['modelo_padrao'];
        $maxTokens = max(256, min(32000, (int) ($cfg->tokens_limite ?: 4000)));

        $response = Http::withToken($key)
            ->timeout(180)
            ->post($meta['endpoint'], [
                'model' => $modelo,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $user],
                ],
                'response_format' => ['type' => 'json_object'],
                'max_tokens' => $maxTokens,
                'temperature' => 0.2,
                'stream' => false,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException(
                "{$meta['nome']} retornou erro ".$response->status().': '.$response->json('error.message', $response->body())
            );
        }

        return [
            'conteudo' => (string) $response->json('choices.0.message.content', ''),
            'tokens_entrada' => $response->json('usage.prompt_tokens'),
            'tokens_saida' => $response->json('usage.completion_tokens'),
            'modelo' => (string) $response->json('model', $modelo),
            'provedor' => $provedor,
        ];
    }
}
