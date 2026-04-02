<?php

namespace App\Support;

class EmpresaContext
{
    private ?int $empresaId = null;

    public function setEmpresaId(?int $empresaId): void
    {
        $this->empresaId = $empresaId;
    }

    public function empresaId(): ?int
    {
        return $this->empresaId;
    }
}
