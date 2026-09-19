<?php

namespace App\Services\Ia;

use App\Models\EmpresaIaConfig;
use App\Models\IaConfigGlobal;
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

    /**
     * Cadeia de resolução: config da empresa → config global → .env.
     *
     * @return array{provedor:string, key:string, modelo:string, cfg:EmpresaIaConfig}
     */
    public function resolucao(int $empresaId): array
    {
        $cfg = $this->configEmpresa($empresaId);
        $global = IaConfigGlobal::atual();

        $provedor = $cfg->provedor_padrao ?: ($global->provedor ?: 'deepseek');

        $key = (string) ($cfg->{$provedor.'_key'} ?? '');
        // Chave global só vale quando o provedor efetivo é o global.
        if ($key === '' && $global->provedor === $provedor) {
            $key = (string) ($global->api_key ?? '');
        }
        if ($key === '') {
            $key = (string) config("services.{$provedor}.key", '');
        }

        $modelo = (string) ($cfg->{$provedor.'_model'} ?? '');
        if ($modelo === '' && $global->provedor === $provedor) {
            $modelo = (string) ($global->modelo ?? '');
        }
        if ($modelo === '') {
            $modelo = (string) config("services.{$provedor}.model", '');
        }

        return [
            'provedor' => $provedor,
            'key' => $key,
            'modelo' => $modelo ?: (self::PROVEDORES[$provedor]['modelo_padrao'] ?? ''),
            'cfg' => $cfg,
        ];
    }

    public function configurado(int $empresaId): bool
    {
        return $this->resolucao($empresaId)['key'] !== '';
    }

    /**
     * Provedor de transcrição de áudio (Whisper): usa o dedicado se
     * configurado, senão o provedor principal global.
     *
     * @return array{provedor:string, key:string, modelo:string}
     */
    public function transcricao(): array
    {
        $global = IaConfigGlobal::atual();
        $provedor = $global->transcricao_provedor ?: ($global->provedor ?: 'deepseek');
        $key = (string) ($global->transcricao_key ?? '');
        if ($key === '' && $provedor === $global->provedor) {
            $key = (string) ($global->api_key ?? '');
        }
        if ($key === '') {
            $key = (string) config("services.{$provedor}.key", '');
        }

        $modelos = ['openai' => 'whisper-1', 'groq' => 'whisper-large-v3-turbo'];

        return [
            'provedor' => $provedor,
            'key' => $key,
            'modelo' => (string) ($global->transcricao_modelo ?? '') ?: ($modelos[$provedor] ?? ''),
        ];
    }

    /**
     * @param  array{recurso?:string, user_id?:int|null}  $contexto
     * @return array{conteudo:string, tokens_entrada:int|null, tokens_saida:int|null, modelo:string, provedor:string}
     */
    public function chatJson(int $empresaId, string $system, string $user, array $contexto = []): array
    {
        $r = $this->resolucao($empresaId);
        $provedor = $r['provedor'];
        $meta = self::PROVEDORES[$provedor] ?? self::PROVEDORES['deepseek'];
        $cfg = $r['cfg'];
        $recurso = (string) ($contexto['recurso'] ?? 'generico');
        $userId = $contexto['user_id'] ?? null;

        $key = $r['key'];
        if ($key === '') {
            throw new RuntimeException(
                "{$meta['nome']} não configurado: informe a API Key em Gerenciar → Integração com IA."
            );
        }

        $this->verificarLimite($empresaId);

        $modelo = $r['modelo'];
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
            'finish_reason' => (string) $response->json('choices.0.finish_reason', ''),
            'tokens_entrada' => $tokensIn,
            'tokens_saida' => $tokensOut,
            'modelo' => $modeloResp,
            'provedor' => $provedor,
        ];
    }

    /**
     * Cota mensal de tokens da empresa (empresas.ia_limite_tokens).
     * NULL = ilimitado. Estourou → bloqueia antes de gastar na API.
     */
    private function verificarLimite(int $empresaId): void
    {
        $limite = DB::table('empresas')->where('id', $empresaId)->value('ia_limite_tokens');
        if ($limite === null) {
            return;
        }

        $usado = (int) DB::table('ia_usos')
            ->where('empresa_id', $empresaId)
            ->where('status', 'ok')
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum(DB::raw('tokens_entrada + tokens_saida'));

        if ($usado >= (int) $limite) {
            throw new RuntimeException(
                'Limite mensal de IA atingido ('.number_format($usado, 0, ',', '.')
                .' de '.number_format((int) $limite, 0, ',', '.')
                .' tokens). Fale com o suporte para ampliar a cota.'
            );
        }
    }

    /**
     * Uso de tokens no mês corrente — usado nas telas de config e monitor.
     *
     * @return array{usado:int, limite:int|null, restante:int|null}
     */
    public function usoMes(int $empresaId): array
    {
        $usado = (int) DB::table('ia_usos')
            ->where('empresa_id', $empresaId)
            ->where('status', 'ok')
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum(DB::raw('tokens_entrada + tokens_saida'));

        $limite = DB::table('empresas')->where('id', $empresaId)->value('ia_limite_tokens');
        $limite = $limite !== null ? (int) $limite : null;

        return [
            'usado' => $usado,
            'limite' => $limite,
            'restante' => $limite !== null ? max(0, $limite - $usado) : null,
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
