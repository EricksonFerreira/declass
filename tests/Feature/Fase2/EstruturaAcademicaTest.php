<?php

use App\Models\AnoLetivo;
use App\Models\Atribuicao;
use App\Models\Disciplina;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Turma;
use App\Support\EmpresaContext;

function setupEstrutura($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Central', 'empresa_id' => $empresa->id]);
    $anoLetivo = AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => 2026, 'empresa_id' => $empresa->id,
    ]);
    $etapa = Etapa::create([
        'nome' => '1º Ano EF', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id,
    ]);
    $disciplina = Disciplina::create(['nome' => 'Matemática', 'sigla' => 'MAT', 'empresa_id' => $empresa->id]);

    return ['escola' => $escola, 'anoLetivo' => $anoLetivo, 'etapa' => $etapa, 'disciplina' => $disciplina];
}

function payloadTurma(array $ids, array $overrides = []): array
{
    return array_merge([
        'escola_id' => $ids['escola']->id,
        'ano_letivo_id' => $ids['anoLetivo']->id,
        'etapa_id' => $ids['etapa']->id,
        'nome' => '1º Ano A',
        'turno' => 'matutino',
        'sala' => '101',
        'capacidade_maxima' => 35,
        'ativo' => 'S',
    ], $overrides);
}

test('ano letivo pode ser criado e e unico por escola', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupEstrutura($empresa);
    $escola = $ids['escola'];

    $this->actingAs($admin)->get(route('ano_letivo.index'))->assertOk();

    $response = $this->actingAs($admin)->post(route('ano_letivo.store'), [
        'escola_id' => $escola->id, 'ano' => 2027, 'status' => 'planejamento', 'ativo' => 'S',
    ]);
    $response->assertSessionHasNoErrors();

    // mesmo ano na mesma escola e rejeitado
    $this->actingAs($admin)->post(route('ano_letivo.store'), [
        'escola_id' => $escola->id, 'ano' => 2027,
    ])->assertSessionHasErrors('ano');
});

test('etapa e disciplina podem ser criadas', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');

    $this->actingAs($admin)->post(route('etapa.store'), [
        'nome' => 'Educação Infantil', 'segmento' => 'infantil', 'ordem' => 1, 'ativo' => 'S',
    ])->assertSessionHasNoErrors();

    $this->actingAs($admin)->post(route('disciplina.store'), [
        'nome' => 'Português', 'sigla' => 'POR', 'ativo' => 'S',
    ])->assertSessionHasNoErrors();

    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    expect(Etapa::count())->toBe(1)->and(Disciplina::count())->toBe(1);
});

test('turma pode ser criada com escola, ano letivo e etapa validos', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupEstrutura($empresa);

    $this->actingAs($admin)->get(route('turma.index'))->assertOk();

    $response = $this->actingAs($admin)->post(route('turma.store'), payloadTurma($ids));
    $response->assertSessionHasNoErrors();

    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $turma = Turma::first();
    expect($turma)->not->toBeNull()
        ->and($turma->nome)->toBe('1º Ano A')
        ->and($turma->escola_id)->toBe($ids['escola']->id)
        ->and($turma->ano_letivo_id)->toBe($ids['anoLetivo']->id);
});

test('turma rejeita ano letivo de outra escola', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupEstrutura($empresa);
    $escolaA = $ids['escola'];
    $anoLetivoA = $ids['anoLetivo'];
    $etapa = $ids['etapa'];

    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $escolaB = Escola::create(['nome' => 'Escola B', 'empresa_id' => $empresa->id]);
    $anoLetivoB = AnoLetivo::create(['escola_id' => $escolaB->id, 'ano' => 2026, 'empresa_id' => $empresa->id]);

    // escola A + ano letivo de B => erro de consistencia
    $response = $this->actingAs($admin)->post(route('turma.store'), payloadTurma([
        'escola' => $escolaA, 'anoLetivo' => $anoLetivoB, 'etapa' => $etapa,
    ]));
    $response->assertSessionHasErrors('ano_letivo_id');
});

test('turma rejeita FKs de outro tenant', function () {
    $empresaA = tenant();
    $empresaB = tenant();
    $adminA = usuarioTenant($empresaA, 'administrador');
    $adminB = usuarioTenant($empresaB, 'administrador');

    $idsA = setupEstrutura($empresaA);
    $idsB = setupEstrutura($empresaB);

    $response = $this->actingAs($adminA)->post(route('turma.store'), payloadTurma([
        'escola' => $idsA['escola'], 'anoLetivo' => $idsA['anoLetivo'], 'etapa' => $idsB['etapa'],
    ]));
    $response->assertSessionHasErrors('etapa_id');
});

test('disciplina pode ser ofertada e removida da turma', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupEstrutura($empresa);

    $this->actingAs($admin)->post(route('turma.store'), payloadTurma($ids));
    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $turma = Turma::first();
    $disc = $ids['disciplina'];

    $this->actingAs($admin)->post(route('turma.disciplina.store', $turma->id), [
        'disciplina_id' => $disc->id, 'carga_horaria_semanal' => 5,
    ])->assertRedirect(route('turma.show', $turma->id));

    expect($turma->fresh()->disciplinas)->toHaveCount(1)
        ->and((int) $turma->fresh()->disciplinas->first()->pivot->carga_horaria_semanal)->toBe(5);

    $this->actingAs($admin)->delete(route('turma.disciplina.destroy', [$turma->id, $disc->id]));
    expect($turma->fresh()->disciplinas)->toHaveCount(0);
});

test('oferta duplicada e atualizada (upsert)', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupEstrutura($empresa);

    $this->actingAs($admin)->post(route('turma.store'), payloadTurma($ids));
    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $turma = Turma::first();
    $disc = $ids['disciplina'];

    $payload = ['disciplina_id' => $disc->id, 'carga_horaria_semanal' => 3];
    $this->actingAs($admin)->post(route('turma.disciplina.store', $turma->id), $payload);
    $this->actingAs($admin)->post(route('turma.disciplina.store', $turma->id), ['disciplina_id' => $disc->id, 'carga_horaria_semanal' => 8]);

    expect(DB::table('turma_disciplina')->count())->toBe(1)
        ->and((int) $turma->fresh()->disciplinas->first()->pivot->carga_horaria_semanal)->toBe(8);
});

test('professor pode ser atribuido a turma', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $prof = usuarioTenant($empresa, 'professor');
    $ids = setupEstrutura($empresa);

    $this->actingAs($admin)->post(route('turma.store'), payloadTurma($ids));
    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $turma = Turma::first();
    $disc = $ids['disciplina'];
    $this->actingAs($admin)->post(route('turma.disciplina.store', $turma->id), ['disciplina_id' => $disc->id]);

    $response = $this->actingAs($admin)->post(route('turma.atribuicao.store', $turma->id), [
        'user_id' => $prof->id, 'disciplina_id' => $disc->id, 'papel' => 'titular',
    ]);
    $response->assertRedirect(route('turma.show', $turma->id));

    expect(Atribuicao::count())->toBe(1)
        ->and(Atribuicao::first()->user_id)->toBe($prof->id);
});

test('atribuicao rejeita usuario de outro tenant', function () {
    $empresaA = tenant();
    $empresaB = tenant();
    $adminA = usuarioTenant($empresaA, 'administrador');
    $profB = usuarioTenant($empresaB, 'professor');
    $ids = setupEstrutura($empresaA);

    $this->actingAs($adminA)->post(route('turma.store'), payloadTurma($ids));
    app(EmpresaContext::class)->setEmpresaId($empresaA->id);
    $turma = Turma::first();

    $this->actingAs($adminA)->post(route('turma.atribuicao.store', $turma->id), [
        'user_id' => $profB->id, 'papel' => 'titular',
    ])->assertSessionHasErrors('user_id');
});

test('professor ve apenas turmas que leciona e nao gerencia estrutura', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $prof = usuarioTenant($empresa, 'professor');
    $ids = setupEstrutura($empresa);

    $this->actingAs($admin)->post(route('turma.store'), payloadTurma($ids));
    $this->actingAs($admin)->post(route('turma.store'), payloadTurma($ids, ['nome' => '2º Ano B', 'sala' => '202']));
    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $turma = Turma::where('nome', '1º Ano A')->first();

    // sem atribuicao: index vazio, show bloqueado
    $this->actingAs($prof)->get(route('turma.index'))->assertOk();
    expect($this->actingAs($prof)->get(route('turma.index'))->viewData('turmas')->total())->toBe(0);
    $this->actingAs($prof)->get(route('turma.show', $turma->id))->assertForbidden();
    $this->actingAs($prof)->get(route('turma.create'))->assertForbidden();
    $this->actingAs($prof)->post(route('turma.store'), payloadTurma($ids))->assertForbidden();
    $this->actingAs($prof)->post(route('etapa.store'), ['nome' => 'X', 'segmento' => 'outro'])->assertForbidden();

    // com atribuicao ativa: show liberado
    \App\Models\Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $turma->id,
        'user_id' => $prof->id, 'papel' => 'titular',
    ]);

    $this->actingAs($prof)->get(route('turma.show', $turma->id))->assertOk();

    // index lista apenas a turma lecionada (a outra fica de fora)
    $response = $this->actingAs($prof)->get(route('turma.index'));
    $turmasVisiveis = $response->viewData('turmas');
    expect($turmasVisiveis->total())->toBe(1)
        ->and($turmasVisiveis->first()->id)->toBe($turma->id);
});

test('turmas tem escopo de tenant', function () {
    $empresaA = tenant();
    $empresaB = tenant();
    $idsA = setupEstrutura($empresaA);
    $idsB = setupEstrutura($empresaB);

    app(EmpresaContext::class)->setEmpresaId($empresaA->id);
    Turma::create(payloadTurma($idsA) + ['empresa_id' => $empresaA->id]);

    app(EmpresaContext::class)->setEmpresaId($empresaB->id);
    Turma::create(payloadTurma($idsB) + ['empresa_id' => $empresaB->id]);

    app(EmpresaContext::class)->setEmpresaId($empresaA->id);
    expect(Turma::count())->toBe(1)
        ->and(Turma::first()->nome)->toBe('1º Ano A');
});
