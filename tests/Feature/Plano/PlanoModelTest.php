<?php

use App\Models\Plano;

describe('Plano model', function () {
    it('retorna desconto anual percentual correto', function () {
        $plano = Plano::factory()->create(['valor_mensal' => 200.00, 'valor_anual' => 160.00]);

        expect($plano->desconto_anual_percentual)->toBe(20);
    });

    it('retorna zero para desconto quando valor_mensal e zero', function () {
        $plano = Plano::factory()->create(['valor_mensal' => 0, 'valor_anual' => 0]);

        expect($plano->desconto_anual_percentual)->toBe(0);
    });

    it('retorna economia_anual correta', function () {
        $plano = Plano::factory()->create(['valor_mensal' => 200.00, 'valor_anual' => 160.00]);

        expect($plano->economia_anual)->toBe(round((200 - 160) * 12, 2));
    });

    it('retorna Ilimitado quando limite_veiculos e null', function () {
        $plano = Plano::factory()->ilimitado()->create();

        expect($plano->limite_veiculos_texto)->toBe('Ilimitado');
    });

    it('retorna o numero como string quando limite_veiculos definido', function () {
        $plano = Plano::factory()->comLimite(15)->create();

        expect($plano->limite_veiculos_texto)->toBe('15');
    });

    it('scopeAtivos retorna apenas planos ativos', function () {
        Plano::factory()->create(['ativo' => true]);
        Plano::factory()->inativo()->create();

        $ativos = Plano::ativos()->get();

        expect($ativos->every(fn ($p) => $p->ativo))->toBeTrue();
    });
});
