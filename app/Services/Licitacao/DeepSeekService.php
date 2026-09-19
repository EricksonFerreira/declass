<?php

namespace App\Services\Licitacao;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Cliente mínimo para a API DeepSeek (compatível com OpenAI
 * Chat Completions). Usa JSON Output nativo para respostas
 * estruturadas.
 */
class DeepSeekService
{
    private const ENDPOINT = 'https://api.deepseek.com/chat/completions';

    public function configurado(): bool
    {
        return (string) config('services.deepseek.key') !== '';
    }

    /**
     * @return array{conteudo:string, tokens_entrada:int|null, tokens_saida:int|null}
     */
    public function chatJson(string $system, string $user, int $maxTokens = 8192): array
    {
        $key = (string) config('services.deepseek.key');
        if ($key === '') {
            throw new RuntimeException('DeepSeek não configurado: defina DEEPSEEK_API_KEY no .env.');
        }

        $modelo = (string) (config('services.deepseek.model') ?: 'deepseek-chat');

        $response = Http::withToken($key)
            ->timeout(180)
            ->post(self::ENDPOINT, [
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
                'DeepSeek retornou erro '.$response->status().': '.$response->json('error.message', $response->body())
            );
        }

        $conteudo = (string) $response->json('choices.0.message.content', '');

        return [
            'conteudo' => $conteudo,
            'tokens_entrada' => $response->json('usage.prompt_tokens'),
            'tokens_saida' => $response->json('usage.completion_tokens'),
            'modelo' => $response->json('model', $modelo),
        ];
    }
}
