<?php

namespace App\Services\Ia;

use App\Models\EmpresaIaConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Cliente de IA multi-provedor (APIs compatíveis com OpenAI Chat
 * Completions). Resolve provedor, chave, modelo e limite de tokens da
 * configuração da empresa — com fallback para as chaves globais do .env.
 * Novos provedores entram incluindo o endpoint em PROVEDORES.
 * Cada chamada é registrada em ia_usos (monitoramento por empresa).
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

    /** USD por 1M de tokens [entrada, saída] — estimativa de custo. */
    private const PRECOS = [
        'deepseek-chat' => [0.28, 0.42],
        'deepseek-reasoner' => [0.55, 2.19],
        'gpt-4o-mini' => [0.15, 0.60],
        'gpt-4o' => [2.50, 10.00],
        'gpt-4.1-mini' => [0.40, 1.60],
        'gpt-4.1' => [2.00, 8.00],
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
     * @param  array{recurso?:string, user_id?:int|null}  $contexto
     * @return array{conteudo:string, tokens_entrada:int|null, tokens_saida:int|null, modelo:string, provedor:string}
     */
    public function chatJson(int $empresaId, string $system, string $user, array $contexto = []): array
    {
        $cfg = $this->configEmpresa($empresaId);
        $provedor = $cfg->provedor_padrao ?: 'deepseek';
        $meta = self::PROVEDORES[$provedor] ?? self::PROVEDORES['deepseek'];
        $recurso = (string) ($contexto['recurso'] ?? 'generico');
        $userId = $contexto['user_id'] ?? null;

        $key = $cfg->chaveDo($provedor);
        if ($key === '') {
            throw new RuntimeException(
                "{$meta['nome']} não configurado: informe a API Key em Gerenciar → Integração com IA."
            );
        }

        $modelo = $cfg->modeloDo($provedor) ?: $meta['modelo_padrao'];
        $maxTokens = max(256, min(32000, (int) ($cfg->tokens_limite ?: 4000)));

        $inicio = microtime(true);

        try {
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
        } catch (\Throwable $e) {
            $this->registrar($empresaId, $userId, $provedor, $modelo, $recurso, 0, 0, $inicio, 'erro', $e->getMessage());
            throw new RuntimeException("{$meta['nome']} falhou na conexão: ".$e->getMessage(), previous: $e);
        }

        $duracao = $inicio;

        if (! $response->successful()) {
            $msg = "{$meta['nome']} retornou erro ".$response->status().': '.$response->json('error.message', $response->body());
            $this->registrar($empresaId, $userId, $provedor, $modelo, $recurso, 0, 0, $duracao, 'erro', $msg);
            throw new RuntimeException($msg);
        }

        $tokensIn = (int) ($response->json('usage.prompt_tokens') ?? 0);
        $tokensOut = (int) ($response->json('usage.completion_tokens') ?? 0);
        $modeloResp = (string) $response->json('model', $modelo);

        $this->registrar($empresaId, $userId, $provedor, $modeloResp, $recurso, $tokensIn, $tokensOut, $duracao, 'ok');

        return [
            'conteudo' => (string) $response->json('choices.0.message.content', ''),
            'tokens_entrada' => $tokensIn,
            'tokens_saida' => $tokensOut,
            'modelo' => $modeloResp,
            'provedor' => $provedor,
        ];
    }

    private function registrar(
        int $empresaId, ?int $userId, string $provedor, string $modelo, string $recurso,
        int $tokensIn, int $tokensOut, float $inicio, string $status, ?string $erro = null
    ): void {
        try {
            DB::table('ia_usos')->insert([
                'empresa_id' => $empresaId,
                'user_id' => $userId,
                'provedor' => $provedor,
                'modelo' => $modelo,
                'recurso' => $recurso,
                'tokens_entrada' => $tokensIn,
                'tokens_saida' => $tokensOut,
                'custo_estimado' => $this->custoEstimado($modelo, $tokensIn, $tokensOut),
                'status' => $status,
                'erro' => $erro ? mb_substr($erro, 0, 500) : null,
                'duracao_ms' => (int) round((microtime(true) - $inicio) * 1000),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable) {
            // Log nunca derruba a chamada principal.
        }
    }

    private function custoEstimado(string $modelo, int $tokensIn, int $tokensOut): ?float
    {
        $preco = self::PRECOS[$modelo] ?? null;
        if (! $preco) {
            return null;
        }

        return round(($tokensIn * $preco[0] + $tokensOut * $preco[1]) / 1_000_000, 6);
    }
}
