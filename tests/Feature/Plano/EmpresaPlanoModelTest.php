<?php

use App\Models\EmpresaPlano;

describe('EmpresaPlano model', function () {
    it('estaAtivo retorna true para status ativo', function () {
        $assinatura = EmpresaPlano::factory()->ativo()->create();

        expect($assinatura->estaAtivo())->toBeTrue();
    });

    it('estaAtivo retorna true para status trial', function () {
        $assinatura = EmpresaPlano::factory()->trial()->create();

        expect($assinatura->estaAtivo())->toBeTrue();
    });

    it('estaAtivo retorna false para status aguardando_pagamento', function () {
        $assinatura = EmpresaPlano::factory()->aguardandoPagamento()->create();

        expect($assinatura->estaAtivo())->toBeFalse();
    });

    it('estaAtivo retorna false para status cancelado', function () {
        $assinatura = EmpresaPlano::factory()->cancelado()->create();

        expect($assinatura->estaAtivo())->toBeFalse();
    });

    it('estaInadimplente retorna true quando status e inadimplente', function () {
        $assinatura = EmpresaPlano::factory()->create(['status' => 'inadimplente']);

        expect($assinatura->estaInadimplente())->toBeTrue();
    });

    it('diasParaVencer retorna numero positivo quando nao vencido', function () {
        $assinatura = EmpresaPlano::factory()->create([
            'data_vencimento' => now()->addDays(10)->toDateString(),
        ]);

        expect($assinatura->diasParaVencer())->toBe(10);
    });

    it('diasParaVencer retorna numero negativo quando ja venceu', function () {
        $assinatura = EmpresaPlano::factory()->create([
            'data_vencimento' => now()->subDays(5)->toDateString(),
        ]);

        expect($assinatura->diasParaVencer())->toBe(-5);
    });

    it('temMudancaAgendada retorna true quando mudanca_tipo e mudanca_efetiva_em definidos', function () {
        $assinatura = EmpresaPlano::factory()->create([
            'mudanca_tipo' => 'cancelamento',
            'mudanca_efetiva_em' => now()->addMonth()->toDateString(),
        ]);

        expect($assinatura->temMudancaAgendada())->toBeTrue();
    });

    it('temMudancaAgendada retorna false quando mudanca_tipo e null', function () {
        $assinatura = EmpresaPlano::factory()->create([
            'mudanca_tipo' => null,
            'mudanca_efetiva_em' => null,
        ]);

        expect($assinatura->temMudancaAgendada())->toBeFalse();
    });

    it('statusBadge retorna label e class corretos', function () {
        $ativo = EmpresaPlano::factory()->ativo()->create();
        $inadimplente = EmpresaPlano::factory()->create(['status' => 'inadimplente']);

        expect($ativo->status_badge['label'])->toBe('Ativo');
        expect($ativo->status_badge['class'])->toBe('badge-success');
        expect($inadimplente->status_badge['class'])->toBe('badge-danger');
    });

    it('periodicidadeTexto retorna Anual para plano anual', function () {
        $assinatura = EmpresaPlano::factory()->anual()->create();

        expect($assinatura->periodicidade_texto)->toBe('Anual');
    });

    it('periodicidadeTexto retorna Mensal para plano mensal', function () {
        $assinatura = EmpresaPlano::factory()->ativo()->create();

        expect($assinatura->periodicidade_texto)->toBe('Mensal');
    });
});
