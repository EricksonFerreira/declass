<?php

namespace App\Modules\Fiscal\DTO;

class DpsDTO
{
    public array $prestador = [];
    public array $tomador = [];
    public string $codigoTributacao;
    public float $valorOperacao;
    public float $ibs = 0.0;
    public float $cbs = 0.0;
    public array $bens = [];

    public function toArray(): array
    {
        return [
            'prestador' => $this->prestador,
            'tomador' => $this->tomador,
            'codigoTributacao' => $this->codigoTributacao,
            'valorOperacao' => $this->valorOperacao,
            'ibs' => $this->ibs,
            'cbs' => $this->cbs,
            'bens' => $this->bens,
        ];
    }
}
