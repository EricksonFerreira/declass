<?php

namespace Database\Factories;

use App\Models\Empresa;
use App\Models\Plano;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmpresaPlanoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'empresa_id'          => Empresa::factory(),
            'plano_id'            => Plano::factory(),
            'periodicidade'       => 'mensal',
            'status'              => 'ativo',
            'metodo_cobranca'     => 'pix',
            'data_inicio'         => now()->toDateString(),
            'data_vencimento'     => now()->addMonth()->toDateString(),
            'valor_cobrado'       => 199.90,
            'renovacao_automatica'=> false,
        ];
    }

    public function ativo(): static
    {
        return $this->state(['status' => 'ativo']);
    }

    public function aguardandoPagamento(): static
    {
        return $this->state(['status' => 'aguardando_pagamento']);
    }

    public function cancelado(): static
    {
        return $this->state(['status' => 'cancelado']);
    }

    public function trial(): static
    {
        return $this->state(['status' => 'trial']);
    }

    public function anual(): static
    {
        return $this->state([
            'periodicidade'  => 'anual',
            'data_vencimento'=> now()->addYear()->toDateString(),
        ]);
    }
}
