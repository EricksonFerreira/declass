<?php

namespace App\Support;

class EscolaContext
{
    private ?int $escolaId = null;

    public function setEscolaId(?int $escolaId): void
    {
        $this->escolaId = $escolaId;
    }

    /**
     * Escola ativa do contexto. Null = todas as escolas do tenant.
     */
    public function escolaId(): ?int
    {
        return $this->escolaId;
    }

    public function filtrar(): bool
    {
        return $this->escolaId !== null;
    }
}
