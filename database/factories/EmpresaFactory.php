<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EmpresaFactory extends Factory
{
    public function definition(): array
    {
        $nome = $this->faker->company();

        return [
            'nome'  => $nome,
            'slug'  => Str::slug($nome) . '-' . $this->faker->unique()->randomNumber(4),
            'ativo' => true,
            'email' => $this->faker->companyEmail(),
        ];
    }
}
