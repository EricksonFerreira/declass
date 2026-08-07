<?php

namespace App\Modules\Fiscal\DTO;

class CalculoTributarioDTO
{
    public function __construct(
        public string $tributo,
        public float $baseCalculo,
        public float $aliquota,
        public float $valor,
        public ?string $cst = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            tributo: $data['tributo'],
            baseCalculo: (float) $data['base_calculo'],
            aliquota: (float) $data['aliquota'],
            valor: (float) $data['valor'],
            cst: $data['cst'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'tributo' => $this->tributo,
            'base_calculo' => $this->baseCalculo,
            'aliquota' => $this->aliquota,
            'valor' => $this->valor,
            'cst' => $this->cst,
        ];
    }
}
