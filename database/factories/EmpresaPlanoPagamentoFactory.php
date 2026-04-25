<?php

namespace Database\Factories;

use App\Models\Empresa;
use App\Models\EmpresaPlano;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmpresaPlanoPagamentoFactory extends Factory
{
    public function definition(): array
    {
        $assinatura = EmpresaPlano::factory()->create();

        return [
            'empresa_plano_id' => $assinatura->id,
            'empresa_id'       => $assinatura->empresa_id,
            'valor'            => 199.90,
            'periodicidade'    => 'mensal',
            'metodo'           => 'pix',
            'status'           => 'pendente',
            'data_vencimento'  => now()->addDays(3)->toDateString(),
            'metadados'        => ['acao' => 'contratacao'],
        ];
    }

    public function pago(): static
    {
        return $this->state([
            'status'  => 'pago',
            'pago_em' => now(),
        ]);
    }

    public function pendente(): static
    {
        return $this->state(['status' => 'pendente']);
    }

    public function upgrade(): static
    {
        return $this->state([
            'metadados' => ['acao' => 'upgrade'],
        ]);
    }
}
