<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PlanoFactory extends Factory
{
    public function definition(): array
    {
        $nome = $this->faker->unique()->word().' '.$this->faker->randomElement(['Básico', 'Pro', 'Premium']);

        return [
            'nome' => $nome,
            'slug' => Str::slug($nome),
            'descricao' => $this->faker->sentence(),
            'valor_mensal' => $this->faker->randomFloat(2, 50, 500),
            'valor_anual' => $this->faker->randomFloat(2, 40, 450),
            'limite_veiculos' => $this->faker->randomElement([5, 10, 20, 50, null]),
            'site_vendas' => false,
            'modulo_multas' => false,
            'modulo_nfse' => false,
            'multi_estacao' => false,
            'api_integracao' => false,
            'ativo' => true,
            'destaque' => false,
            'ordem' => $this->faker->numberBetween(1, 10),
        ];
    }

    public function ilimitado(): static
    {
        return $this->state(['limite_veiculos' => null, 'limite_usuarios' => null]);
    }

    public function comLimite(int $limite): static
    {
        return $this->state(['limite_veiculos' => $limite, 'limite_usuarios' => $limite]);
    }

    public function inativo(): static
    {
        return $this->state(['ativo' => false]);
    }
}
