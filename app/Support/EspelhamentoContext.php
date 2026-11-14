<?php

namespace App\Support;

use App\Models\User;

/**
 * Espelhamento de acesso (somente superadmin).
 *
 * Diferente de impersonation: o usuario autenticado NUNCA muda —
 * auth()->id() continua sendo o do superadmin, entao acoes e trilhas
 * de auditoria seguem registradas em nome dele. O que muda e a
 * *resolucao de permissoes*: hasPermissao/isProfessor/isAdministrador
 * delegam para o usuario espelhado enquanto a sessao estiver ativa.
 */
class EspelhamentoContext
{
    public const SESSAO_USUARIO = 'espelhando_usuario_id';

    public const SESSAO_EMPRESA_ANTERIOR = 'espelhando_empresa_context_anterior';

    private ?User $alvo = null;

    private bool $alvoResolvido = false;

    public function ativo(): bool
    {
        return $this->alvoId() !== null;
    }

    public function alvoId(): ?int
    {
        $id = (int) session(self::SESSAO_USUARIO, 0);

        return $id > 0 ? $id : null;
    }

    public function alvo(): ?User
    {
        if (! $this->alvoResolvido) {
            $this->alvoResolvido = true;
            $id = $this->alvoId();
            $this->alvo = $id ? User::query()->find($id) : null;
        }

        return $this->alvo;
    }

    /** Leitura por propriedade em Blade: ->ativo, ->alvo, ->alvo_id. */
    public function __get(string $prop)
    {
        return match ($prop) {
            'ativo' => $this->ativo(),
            'alvo' => $this->alvo(),
            'alvo_id' => $this->alvoId(),
            default => null,
        };
    }

    /**
     * Guarda o tenant observado antes de fixar o tenant do alvo —
     * ao encerrar, a observacao anterior e restaurada.
     */
    public function iniciar(User $alvo): void
    {
        if (! session()->has(self::SESSAO_EMPRESA_ANTERIOR)) {
            session()->put(self::SESSAO_EMPRESA_ANTERIOR, (int) session('empresa_context_id', 0));
        }

        session()->put(self::SESSAO_USUARIO, $alvo->id);

        if ($alvo->empresa_id) {
            session()->put('empresa_context_id', (int) $alvo->empresa_id);
        }

        // Escola da sessao anterior pode nao pertencer ao tenant do alvo.
        session()->forget('escola_contexto_id');
    }

    public function parar(): void
    {
        session()->forget(self::SESSAO_USUARIO);

        $empresaAnterior = (int) session(self::SESSAO_EMPRESA_ANTERIOR, 0);
        session()->forget(self::SESSAO_EMPRESA_ANTERIOR);
        session()->put('empresa_context_id', $empresaAnterior);
        session()->forget('escola_contexto_id');

        $this->alvo = null;
        $this->alvoResolvido = false;
    }
}
