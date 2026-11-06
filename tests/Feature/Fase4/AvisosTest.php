<?php

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\Aviso;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Matricula;
use App\Models\Pessoa;
use App\Models\Responsavel;
use App\Models\Turma;
use App\Support\EmpresaContext;

function setupAvisos($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Central', 'empresa_id' => $empresa->id]);
    $anoLetivo = AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => 2026, 'empresa_id' => $empresa->id,
    ]);
    $etapa = Etapa::create([
        'nome' => '1º Ano EF', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id,
    ]);
    $turmaA = Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id, 'nome' => '1º Ano A', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);
    $turmaB = Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id, 'nome' => '1º Ano B', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);

    $pessoaAluno = Pessoa::create(['nome' => 'Maria Silva', 'empresa_id' => $empresa->id]);
    $aluno = Aluno::create([
        'pessoa_id' => $pessoaAluno->id, 'escola_id' => $escola->id,
        'registro_academico' => 'RA-0001', 'empresa_id' => $empresa->id,
    ]);
    Matricula::create([
        'aluno_id' => $aluno->id, 'turma_id' => $turmaA->id,
        'ano_letivo_id' => $anoLetivo->id, 'status' => 'ativa',
        'numero_chamada' => 1, 'empresa_id' => $empresa->id,
    ]);

    $pessoaResp = Pessoa::create(['nome' => 'João Pai', 'empresa_id' => $empresa->id]);
    $responsavel = Responsavel::create([
        'pessoa_id' => $pessoaResp->id, 'empresa_id' => $empresa->id,
    ]);
    $aluno->responsaveis()->attach($responsavel->id, [
        'tipo' => 'legal', 'principal' => true,
        'pode_retirar' => true, 'autoriza_contato' => true,
    ]);

    return compact('escola', 'anoLetivo', 'etapa', 'turmaA', 'turmaB', 'aluno', 'responsavel');
}

function avisoDemo(array $ids, array $overrides = []): Aviso
{
    return Aviso::create(array_merge([
        'empresa_id' => $ids['empresa']->id ?? null,
        'escola_id' => $ids['escola']->id,
        'turma_id' => null,
        'titulo' => 'Aviso teste',
        'mensagem' => 'Mensagem do aviso',
        'tipo' => 'info',
        'data_publicacao' => now()->toDateString(),
        'ativo' => 'S',
    ], $overrides));
}

test('secretaria cria e lista avisos', function () {
    $empresa = tenant();
    $ids = setupAvisos($empresa);
    $ids['empresa'] = $empresa;

    $user = usuarioTenant($empresa, 'secretario');

    $this->actingAs($user)->post(route('aviso.store'), [
        'escola_id' => $ids['escola']->id,
        'titulo' => 'Reunião de pais',
        'mensagem' => 'Reunião no dia 20.',
        'tipo' => 'evento',
        'data_publicacao' => now()->toDateString(),
        'ativo' => 'S',
    ])->assertRedirect();

    $this->assertDatabaseHas('avisos', ['titulo' => 'Reunião de pais']);

    $this->actingAs($user)
        ->get(route('aviso.index'))
        ->assertOk()
        ->assertSee('Reunião de pais');
});

test('aviso geral aparece no portal do responsavel', function () {
    $empresa = tenant();
    $ids = setupAvisos($empresa);
    $ids['empresa'] = $empresa;

    avisoDemo($ids, ['titulo' => 'Aviso geral escola']);

    $user = usuarioTenant($empresa, 'responsavel_legal');
    $ids['responsavel']->update(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('portal.avisos'))
        ->assertOk()
        ->assertSee('Aviso geral escola');
});

test('aviso de outra turma nao aparece no portal', function () {
    $empresa = tenant();
    $ids = setupAvisos($empresa);
    $ids['empresa'] = $empresa;

    avisoDemo($ids, [
        'titulo' => 'Aviso so turma B',
        'turma_id' => $ids['turmaB']->id,
    ]);
    avisoDemo($ids, ['titulo' => 'Aviso da turma A', 'turma_id' => $ids['turmaA']->id]);

    $user = usuarioTenant($empresa, 'responsavel_legal');
    $ids['responsavel']->update(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('portal.avisos'))
        ->assertOk()
        ->assertSee('Aviso da turma A')
        ->assertDontSee('Aviso so turma B');
});

test('aviso inativo ou futuro nao aparece no portal', function () {
    $empresa = tenant();
    $ids = setupAvisos($empresa);
    $ids['empresa'] = $empresa;

    avisoDemo($ids, ['titulo' => 'Aviso inativo', 'ativo' => 'N']);
    avisoDemo($ids, [
        'titulo' => 'Aviso futuro',
        'data_publicacao' => now()->addDays(5)->toDateString(),
    ]);

    $user = usuarioTenant($empresa, 'responsavel_legal');
    $ids['responsavel']->update(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('portal.avisos'))
        ->assertOk()
        ->assertDontSee('Aviso inativo')
        ->assertDontSee('Aviso futuro');
});

test('aviso de outro tenant nao vaza no portal', function () {
    $empresaA = tenant();
    $idsA = setupAvisos($empresaA);
    $idsA['empresa'] = $empresaA;
    avisoDemo($idsA, ['titulo' => 'Aviso tenant A']);

    // Tenant B com sua propria estrutura e responsavel
    $empresaB = tenant(['nome' => 'Escola B', 'slug' => 'escola-b']);
    $idsB = setupAvisos($empresaB);
    $idsB['empresa'] = $empresaB;

    $userB = usuarioTenant($empresaB, 'responsavel_legal');
    $idsB['responsavel']->update(['user_id' => $userB->id]);

    $this->actingAs($userB)
        ->get(route('portal.avisos'))
        ->assertOk()
        ->assertDontSee('Aviso tenant A');
});

test('aviso do mural interno nao aparece no portal das familias', function () {
    $empresa = tenant();
    $ids = setupAvisos($empresa);
    $ids['empresa'] = $empresa;

    avisoDemo($ids, ['titulo' => 'Aviso para familias']);
    avisoDemo($ids, ['titulo' => 'Comunicado da equipe', 'destino' => 'staff']);

    $user = usuarioTenant($empresa, 'responsavel_legal');
    $ids['responsavel']->update(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('portal.avisos'))
        ->assertOk()
        ->assertSee('Aviso para familias')
        ->assertDontSee('Comunicado da equipe');

    // A equipe ve os dois na listagem interna
    $this->actingAs(usuarioTenant($empresa, 'secretario'))
        ->get(route('aviso.index'))
        ->assertOk()
        ->assertSee('Aviso para familias')
        ->assertSee('Comunicado da equipe');
});

test('aviso aceita destino staff e rejeita destino invalido', function () {
    $empresa = tenant();
    $ids = setupAvisos($empresa);
    $ids['empresa'] = $empresa;

    $user = usuarioTenant($empresa, 'secretario');

    $this->actingAs($user)->post(route('aviso.store'), [
        'escola_id' => $ids['escola']->id,
        'titulo' => 'Aviso interno',
        'mensagem' => 'Somente a equipe.',
        'tipo' => 'info',
        'destino' => 'staff',
        'data_publicacao' => now()->toDateString(),
        'ativo' => 'S',
    ])->assertRedirect();

    $this->assertDatabaseHas('avisos', ['titulo' => 'Aviso interno', 'destino' => 'staff']);

    // sem destino informado, assume familias
    $this->actingAs($user)->post(route('aviso.store'), [
        'escola_id' => $ids['escola']->id,
        'titulo' => 'Aviso padrao',
        'mensagem' => 'Para as familias.',
        'tipo' => 'info',
        'data_publicacao' => now()->toDateString(),
        'ativo' => 'S',
    ])->assertRedirect();

    $this->assertDatabaseHas('avisos', ['titulo' => 'Aviso padrao', 'destino' => 'familias']);

    $this->actingAs($user)->post(route('aviso.store'), [
        'escola_id' => $ids['escola']->id,
        'titulo' => 'Destino ruim',
        'mensagem' => 'Invalido.',
        'tipo' => 'info',
        'destino' => 'inexistente',
        'data_publicacao' => now()->toDateString(),
        'ativo' => 'S',
    ])->assertSessionHasErrors('destino');
});

test('professor le avisos mas nao cria', function () {
    $empresa = tenant();
    $ids = setupAvisos($empresa);
    $ids['empresa'] = $empresa;
    avisoDemo($ids);

    $prof = usuarioTenant($empresa, 'professor');

    $this->actingAs($prof)
        ->get(route('aviso.index'))
        ->assertOk()
        ->assertSee('Aviso teste');

    $this->actingAs($prof)
        ->get(route('aviso.create'))
        ->assertForbidden();
});
