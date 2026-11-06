<?php

use App\Models\Escola;
use App\Models\Organizacao;
use App\Models\User;
use App\Support\EmpresaContext;

test('tenant scope isolates escolas between empresas', function () {
    $empresaA = tenant();
    $empresaB = tenant();

    Escola::create(['empresa_id' => $empresaA->id, 'nome' => 'Escola A']);
    Escola::create(['empresa_id' => $empresaB->id, 'nome' => 'Escola B']);

    app(EmpresaContext::class)->setEmpresaId($empresaA->id);

    $escolas = Escola::all();
    expect($escolas)->toHaveCount(1)
        ->and($escolas->first()->nome)->toBe('Escola A');
});

test('tenant scope isolates organizacoes between empresas', function () {
    $empresaA = tenant();
    $empresaB = tenant();

    Organizacao::create(['empresa_id' => $empresaA->id, 'nome' => 'Rede A', 'tipo' => 'rede']);
    Organizacao::create(['empresa_id' => $empresaB->id, 'nome' => 'Rede B', 'tipo' => 'rede']);

    app(EmpresaContext::class)->setEmpresaId($empresaB->id);

    $organizacoes = Organizacao::all();
    expect($organizacoes)->toHaveCount(1)
        ->and($organizacoes->first()->nome)->toBe('Rede B');
});

test('tenant scope isolates users between empresas', function () {
    $empresaA = tenant();
    $empresaB = tenant();

    User::factory()->create(['empresa_id' => $empresaA->id]);
    User::factory()->create(['empresa_id' => $empresaB->id]);

    app(EmpresaContext::class)->setEmpresaId($empresaA->id);

    expect(User::count())->toBe(1);
});

test('escola slug is unique per tenant but may repeat across tenants', function () {
    $empresaA = tenant();
    $empresaB = tenant();

    $escolaA = Escola::create(['empresa_id' => $empresaA->id, 'nome' => 'Unidade Central']);
    $escolaB = Escola::create(['empresa_id' => $empresaB->id, 'nome' => 'Unidade Central']);

    expect($escolaA->slug)->toBe('unidade-central')
        ->and($escolaB->slug)->toBe('unidade-central');

    // Dentro do mesmo tenant, nome repetido gera slug sufixado.
    $escolaA2 = Escola::create(['empresa_id' => $empresaA->id, 'nome' => 'Unidade Central']);
    expect($escolaA2->slug)->not->toBe('unidade-central');
});

test('creating records auto-fills empresa_id from context', function () {
    $empresa = tenant();
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Sem empresa explicita']);

    expect($escola->empresa_id)->toBe($empresa->id);
});
