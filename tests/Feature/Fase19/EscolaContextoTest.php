<?php

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Matricula;
use App\Models\Pessoa;
use App\Models\Turma;
use App\Support\EmpresaContext;
use App\Support\EscolaContext;

function escolaContextoFixture($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escolaA = Escola::create(['nome' => 'Unidade A', 'empresa_id' => $empresa->id, 'ativa' => 'S']);
    $escolaB = Escola::create(['nome' => 'Unidade B', 'empresa_id' => $empresa->id, 'ativa' => 'S']);

    $anoLetivo = AnoLetivo::create([
        'escola_id' => $escolaA->id, 'ano' => 2026, 'empresa_id' => $empresa->id,
    ]);
    $etapa = Etapa::create([
        'nome' => '1º Ano EF', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id,
    ]);

    $turmaA = Turma::create([
        'escola_id' => $escolaA->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id, 'nome' => '1º Ano A', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);
    $turmaB = Turma::create([
        'escola_id' => $escolaB->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id, 'nome' => '1º Ano B', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);

    $pessoaA = Pessoa::create(['nome' => 'Aluno A', 'empresa_id' => $empresa->id]);
    $alunoA = Aluno::create([
        'pessoa_id' => $pessoaA->id, 'escola_id' => $escolaA->id,
        'registro_academico' => 'RA-A1', 'empresa_id' => $empresa->id,
    ]);
    Matricula::create([
        'aluno_id' => $alunoA->id, 'turma_id' => $turmaA->id,
        'ano_letivo_id' => $anoLetivo->id, 'status' => 'ativa',
        'numero_chamada' => 1, 'empresa_id' => $empresa->id,
    ]);

    $pessoaB = Pessoa::create(['nome' => 'Aluno B', 'empresa_id' => $empresa->id]);
    $alunoB = Aluno::create([
        'pessoa_id' => $pessoaB->id, 'escola_id' => $escolaB->id,
        'registro_academico' => 'RA-B1', 'empresa_id' => $empresa->id,
    ]);
    Matricula::create([
        'aluno_id' => $alunoB->id, 'turma_id' => $turmaB->id,
        'ano_letivo_id' => $anoLetivo->id, 'status' => 'ativa',
        'numero_chamada' => 1, 'empresa_id' => $empresa->id,
    ]);

    return compact('escolaA', 'escolaB', 'turmaA', 'turmaB', 'alunoA', 'alunoB');
}

test('contexto padrao mostra todas as escolas e dados', function () {
    $empresa = tenant();
    ['escolaA' => $escolaA, 'escolaB' => $escolaB, 'alunoA' => $alunoA, 'alunoB' => $alunoB] = escolaContextoFixture($empresa);

    // Sem escola no contexto: nada filtrado.
    expect(app(EscolaContext::class)->escolaId())->toBeNull();
    expect(Aluno::count())->toBe(2);
    expect(Escola::count())->toBe(2);
});

test('selecionar escola filtra alunos e matriculas na sessao', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'administrador');
    ['escolaA' => $escolaA, 'alunoA' => $alunoA] = escolaContextoFixture($empresa);

    $this->actingAs($user)
        ->post('/admin/escola-contexto', ['escola_id' => $escolaA->id])
        ->assertSessionHas('success');

    expect(session('escola_contexto_id'))->toBe($escolaA->id);

    // Proxima request resolve o contexto e o escopo global filtra.
    $this->actingAs($user)->get('/admin/home')->assertOk();
    expect(app(EscolaContext::class)->escolaId())->toBe($escolaA->id);
    expect(Aluno::pluck('id')->all())->toBe([$alunoA->id]);
    expect(Matricula::count())->toBe(1);
});

test('voltar para todas limpa o filtro', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'administrador');
    ['escolaA' => $escolaA, 'alunoB' => $alunoB] = escolaContextoFixture($empresa);

    $this->actingAs($user)->post('/admin/escola-contexto', ['escola_id' => $escolaA->id]);
    $this->actingAs($user)->post('/admin/escola-contexto', ['escola_id' => 0]);

    expect(session('escola_contexto_id'))->toBe(0);

    $this->actingAs($user)->get('/admin/home')->assertOk();
    expect(app(EscolaContext::class)->escolaId())->toBeNull();
    expect(Aluno::count())->toBe(2);
});

test('escola de outro tenant e rejeitada', function () {
    $empresa = tenant();
    $empresaB = tenant();
    $user = usuarioTenant($empresa, 'administrador');

    app(EmpresaContext::class)->setEmpresaId($empresaB->id);
    $escolaFora = Escola::create(['nome' => 'Fora', 'empresa_id' => $empresaB->id]);

    $this->actingAs($user)
        ->post('/admin/escola-contexto', ['escola_id' => $escolaFora->id])
        ->assertSessionHas('error');

    expect(session('escola_contexto_id'))->toBeNull();
});

test('usuario com vinculo em user_escolas so seleciona escolas atribuidas', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'professor');
    ['escolaA' => $escolaA, 'escolaB' => $escolaB] = escolaContextoFixture($empresa);

    // Professor so atua na unidade A.
    $user->escolas()->sync([$escolaA->id]);

    $this->actingAs($user)
        ->post('/admin/escola-contexto', ['escola_id' => $escolaB->id])
        ->assertSessionHas('error');
    expect(session('escola_contexto_id'))->toBeNull();

    $this->actingAs($user)
        ->post('/admin/escola-contexto', ['escola_id' => $escolaA->id])
        ->assertSessionHas('success');
    expect(session('escola_contexto_id'))->toBe($escolaA->id);
});

test('sessao com escola nao atribuida e ignorada na resolucao', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'professor');
    ['escolaA' => $escolaA, 'escolaB' => $escolaB] = escolaContextoFixture($empresa);

    $user->escolas()->sync([$escolaA->id]);

    // Forca a sessao direto (simula vinculo removido apos selecao).
    session(['escola_contexto_id' => $escolaB->id]);
    $this->actingAs($user)->get('/admin/home')->assertOk();

    expect(app(EscolaContext::class)->escolaId())->toBeNull();
});

test('rotas de organizacao nao existem mais', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'administrador');

    $this->actingAs($user)->get('/admin/organizacao')->assertNotFound();
    $this->actingAs($user)->post('/admin/escola-contexto', ['escola_id' => 999])->assertSessionHas('error');
});
