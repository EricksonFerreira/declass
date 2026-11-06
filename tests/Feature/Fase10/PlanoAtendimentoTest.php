<?php

use App\Models\Aluno;
use App\Models\Escola;
use App\Models\Pessoa;
use App\Models\PlanoAtendimento;
use App\Support\EmpresaContext;

function setupPlano($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Apoio', 'empresa_id' => $empresa->id]);
    $pessoa = Pessoa::create(['nome' => 'Aluno PEI', 'empresa_id' => $empresa->id]);
    $aluno = Aluno::create([
        'empresa_id' => $empresa->id, 'pessoa_id' => $pessoa->id,
        'escola_id' => $escola->id, 'registro_academico' => 'RA-P1', 'ativo' => 'S',
    ]);

    return compact('escola', 'aluno');
}

test('atendimento_especializado gerencia planos PEI/PDI', function () {
    $empresa = tenant();
    $ids = setupPlano($empresa);
    $user = usuarioTenant($empresa, 'atendimento_especializado');

    $this->actingAs($user)->get(route('plano_atendimento.index'))->assertOk();
    $this->actingAs($user)->get(route('plano_atendimento.create'))->assertOk();

    $this->actingAs($user)->post(route('plano_atendimento.store'), [
        'aluno_id' => $ids['aluno']->id,
        'escola_id' => $ids['escola']->id,
        'tipo' => 'pei',
        'status' => 'ativo',
        'data_elaboracao' => '2026-03-01',
        'proxima_revisao' => '2026-12-01',
        'diagnostico' => 'TEA nivel 1',
        'objetivos' => 'Autonomia nas atividades',
        'estrategias' => 'Rotina visual e tempo estendido',
        'ativo' => 'S',
    ])->assertRedirect();

    $plano = PlanoAtendimento::first();
    expect($plano)->not->toBeNull()
        ->and($plano->tipo)->toBe('pei')
        ->and($plano->status)->toBe('ativo');
});

test('tipo e status invalidos falham na validacao', function () {
    $empresa = tenant();
    $ids = setupPlano($empresa);
    $user = usuarioTenant($empresa, 'administrador');

    $this->actingAs($user)->post(route('plano_atendimento.store'), [
        'aluno_id' => $ids['aluno']->id,
        'escola_id' => $ids['escola']->id,
        'tipo' => 'xyz',
        'status' => 'inexistente',
        'objetivos' => 'Teste',
    ])->assertSessionHasErrors(['tipo', 'status']);
});

test('revisao anterior a elaboracao e rejeitada', function () {
    $empresa = tenant();
    $ids = setupPlano($empresa);
    $user = usuarioTenant($empresa, 'administrador');

    $this->actingAs($user)->post(route('plano_atendimento.store'), [
        'aluno_id' => $ids['aluno']->id,
        'escola_id' => $ids['escola']->id,
        'tipo' => 'pdi',
        'data_elaboracao' => '2026-06-01',
        'proxima_revisao' => '2026-01-01',
        'objetivos' => 'Teste',
    ])->assertSessionHasErrors('proxima_revisao');
});

test('planos tem escopo de tenant', function () {
    $empresa = tenant();
    $ids = setupPlano($empresa);
    PlanoAtendimento::create([
        'empresa_id' => $empresa->id, 'aluno_id' => $ids['aluno']->id,
        'escola_id' => $ids['escola']->id, 'tipo' => 'pei', 'status' => 'ativo',
        'objetivos' => 'PLANO-SECRETO', 'ativo' => 'S',
    ]);

    $outro = tenant(['nome' => 'Rede E', 'slug' => 'rede-e']);
    app(EmpresaContext::class)->setEmpresaId($outro->id);
    expect(PlanoAtendimento::count())->toBe(0);

    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $user = usuarioTenant($empresa, 'administrador');
    $this->actingAs($user)->get(route('plano_atendimento.index'))
        ->assertOk();
});

test('professor nao acessa planos de atendimento', function () {
    $empresa = tenant();
    setupPlano($empresa);
    $user = usuarioTenant($empresa, 'professor');

    $this->actingAs($user)->get(route('plano_atendimento.index'))
        ->assertForbidden();
    $this->actingAs($user)->get(route('plano_atendimento.create'))
        ->assertForbidden();
});

test('coordenador le mas nao gerencia planos', function () {
    $empresa = tenant();
    $ids = setupPlano($empresa);
    $user = usuarioTenant($empresa, 'coordenador_pedagogico');

    $this->actingAs($user)->get(route('plano_atendimento.index'))->assertOk();
    $this->actingAs($user)->get(route('plano_atendimento.create'))->assertForbidden();
});
