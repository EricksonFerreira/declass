<?php

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Matricula;
use App\Models\Pessoa;
use App\Models\Turma;
use App\Support\EmpresaContext;

function setupMatricula($empresa, string $nomeAluno = 'Maria Silva'): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Central', 'empresa_id' => $empresa->id]);
    $anoLetivo = AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => 2026, 'empresa_id' => $empresa->id,
    ]);
    $etapa = Etapa::create([
        'nome' => '1º Ano EF', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id,
    ]);
    $turma = Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id, 'nome' => '1º Ano A', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);
    $pessoa = Pessoa::create(['nome' => $nomeAluno, 'empresa_id' => $empresa->id]);
    $aluno = Aluno::create([
        'pessoa_id' => $pessoa->id, 'escola_id' => $escola->id,
        'registro_academico' => 'RA-0001', 'empresa_id' => $empresa->id,
    ]);

    return compact('escola', 'anoLetivo', 'etapa', 'turma', 'pessoa', 'aluno');
}

function payloadMatricula(array $ids, array $overrides = []): array
{
    return array_merge([
        'aluno_id' => $ids['aluno']->id,
        'turma_id' => $ids['turma']->id,
        'status' => 'ativa',
        'numero_chamada' => 1,
        'data_matricula' => '2026-02-01',
    ], $overrides);
}

test('matricula pode ser criada e resolve ano letivo da turma', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupMatricula($empresa);

    $this->actingAs($admin)->get(route('matricula.index'))->assertOk();

    $response = $this->actingAs($admin)->post(route('matricula.store'), payloadMatricula($ids));
    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('matricula.index'));

    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $matricula = Matricula::first();
    expect($matricula)->not->toBeNull()
        ->and($matricula->ano_letivo_id)->toBe($ids['turma']->ano_letivo_id)
        ->and($matricula->status)->toBe('ativa')
        ->and($matricula->aluno->pessoa->nome)->toBe('Maria Silva');
});

test('matricula duplicada para o mesmo aluno e turma e rejeitada', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupMatricula($empresa);

    $this->actingAs($admin)->post(route('matricula.store'), payloadMatricula($ids));

    $this->actingAs($admin)->post(route('matricula.store'), payloadMatricula($ids, ['numero_chamada' => 2]))
        ->assertSessionHasErrors();
});

test('numero de chamada e unico por turma', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupMatricula($empresa);

    // segundo aluno na mesma turma
    $pessoa2 = Pessoa::create(['nome' => 'João Souza', 'empresa_id' => $empresa->id]);
    $aluno2 = Aluno::create([
        'pessoa_id' => $pessoa2->id, 'escola_id' => $ids['escola']->id,
        'registro_academico' => 'RA-0002', 'empresa_id' => $empresa->id,
    ]);

    $this->actingAs($admin)->post(route('matricula.store'), payloadMatricula($ids));
    $this->actingAs($admin)->post(route('matricula.store'), payloadMatricula($ids, [
        'aluno_id' => $aluno2->id,
    ]))->assertSessionHasErrors('numero_chamada');
});

test('matricula rejeita aluno e turma de outro tenant', function () {
    $empresaA = tenant();
    $empresaB = tenant();
    $admin = usuarioTenant($empresaA, 'administrador');
    $idsA = setupMatricula($empresaA);

    app(EmpresaContext::class)->setEmpresaId($empresaB->id);
    $idsB = setupMatricula($empresaB, 'Aluno B');

    app(EmpresaContext::class)->setEmpresaId($empresaA->id);

    // aluno de B + turma de A
    $this->actingAs($admin)->post(route('matricula.store'), payloadMatricula($idsA, [
        'aluno_id' => $idsB['aluno']->id,
    ]))->assertSessionHasErrors('aluno_id');

    // aluno de A + turma de B
    $this->actingAs($admin)->post(route('matricula.store'), payloadMatricula($idsB, [
        'aluno_id' => $idsA['aluno']->id,
    ]))->assertSessionHasErrors('turma_id');
});

test('matricula pode ser atualizada (status e saida) e excluida', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupMatricula($empresa);

    $this->actingAs($admin)->post(route('matricula.store'), payloadMatricula($ids));

    $matricula = Matricula::first();

    $this->actingAs($admin)->put(route('matricula.update', $matricula->id), [
        'status' => 'trancada',
        'data_saida' => '2026-06-01',
        'observacoes' => 'Trancamento a pedido da família',
    ])->assertSessionHasNoErrors();

    expect($matricula->fresh()->status)->toBe('trancada')
        ->and($matricula->fresh()->data_saida->format('Y-m-d'))->toBe('2026-06-01');

    $this->actingAs($admin)->get(route('matricula.index'));

    $this->actingAs($admin)->delete(route('matricula.destroy', $matricula->id))
        ->assertRedirect(route('matricula.index'));

    expect(Matricula::withTrashed()->find($matricula->id)->trashed())->toBeTrue();
});

test('edicao nao permite trocar aluno ou turma', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupMatricula($empresa);
    $turma2 = Turma::create([
        'escola_id' => $ids['escola']->id, 'ano_letivo_id' => $ids['anoLetivo']->id,
        'etapa_id' => $ids['etapa']->id, 'nome' => '1º Ano B', 'turno' => 'vespertino',
        'empresa_id' => $empresa->id,
    ]);

    $this->actingAs($admin)->post(route('matricula.store'), payloadMatricula($ids));
    $matricula = Matricula::first();

    $this->actingAs($admin)->put(route('matricula.update', $matricula->id), [
        'status' => 'ativa',
        'turma_id' => $turma2->id, // ignorado: update nao tem regra para turma_id
    ])->assertSessionHasNoErrors();

    expect($matricula->fresh()->turma_id)->toBe($ids['turma']->id);
});

test('matriculas tem escopo de tenant', function () {
    $empresa = tenant();
    $ids = setupMatricula($empresa);
    Matricula::create([
        'aluno_id' => $ids['aluno']->id, 'turma_id' => $ids['turma']->id,
        'ano_letivo_id' => $ids['anoLetivo']->id, 'status' => 'ativa',
        'empresa_id' => $empresa->id,
    ]);

    $empresaB = tenant();
    app(EmpresaContext::class)->setEmpresaId($empresaB->id);

    expect(Matricula::count())->toBe(0);
});

test('professor pode listar matriculas mas nao criar', function () {
    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');
    $ids = setupMatricula($empresa);

    $this->actingAs($professor)->get(route('matricula.index'))->assertOk();

    $this->actingAs($professor)->post(route('matricula.store'), payloadMatricula($ids))
        ->assertForbidden();
});

test('turma show exibe alunos matriculados', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupMatricula($empresa);

    $this->actingAs($admin)->post(route('matricula.store'), payloadMatricula($ids));

    $this->actingAs($admin)->get(route('turma.show', $ids['turma']->id))
        ->assertOk()
        ->assertSee('Maria Silva');
});
