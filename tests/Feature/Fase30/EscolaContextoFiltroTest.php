<?php

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Matricula;
use App\Models\Pessoa;
use App\Models\Turma;
use App\Support\EmpresaContext;

function escolaFiltroFixture($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escolaA = Escola::create(['nome' => 'Unidade Filtro A', 'empresa_id' => $empresa->id, 'ativa' => 'S']);
    $escolaB = Escola::create(['nome' => 'Unidade Filtro B', 'empresa_id' => $empresa->id, 'ativa' => 'S']);

    $anoLetivo = AnoLetivo::create([
        'escola_id' => $escolaA->id, 'ano' => 2026, 'empresa_id' => $empresa->id,
    ]);
    $etapa = Etapa::create([
        'nome' => 'Etapa Filtro', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id,
    ]);

    $turmaA = Turma::create([
        'escola_id' => $escolaA->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id, 'nome' => 'Turma Filtro A', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);
    $turmaB = Turma::create([
        'escola_id' => $escolaB->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id, 'nome' => 'Turma Filtro B', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);

    $pessoaA = Pessoa::create(['nome' => 'Aluno Filtro A', 'empresa_id' => $empresa->id]);
    $alunoA = Aluno::create([
        'pessoa_id' => $pessoaA->id, 'escola_id' => $escolaA->id,
        'registro_academico' => 'RA-FA', 'empresa_id' => $empresa->id,
    ]);
    Matricula::create([
        'aluno_id' => $alunoA->id, 'turma_id' => $turmaA->id,
        'ano_letivo_id' => $anoLetivo->id, 'status' => 'ativa',
        'numero_chamada' => 1, 'empresa_id' => $empresa->id,
    ]);

    $pessoaB = Pessoa::create(['nome' => 'Aluno Filtro B', 'empresa_id' => $empresa->id]);
    $alunoB = Aluno::create([
        'pessoa_id' => $pessoaB->id, 'escola_id' => $escolaB->id,
        'registro_academico' => 'RA-FB', 'empresa_id' => $empresa->id,
    ]);
    Matricula::create([
        'aluno_id' => $alunoB->id, 'turma_id' => $turmaB->id,
        'ano_letivo_id' => $anoLetivo->id, 'status' => 'ativa',
        'numero_chamada' => 1, 'empresa_id' => $empresa->id,
    ]);

    return compact('escolaA', 'escolaB', 'turmaA', 'turmaB', 'alunoA', 'alunoB');
}

test('com escola no contexto o filtro de escola some do index', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'administrador');
    ['escolaA' => $escolaA] = escolaFiltroFixture($empresa);

    $this->actingAs($user)->post('/admin/escola-contexto', ['escola_id' => $escolaA->id]);

    $this->actingAs($user)->get('/admin/aluno')
        ->assertOk()
        ->assertDontSee('name="escola_id_', false);
});

test('sem escola no contexto o filtro de escola aparece no index', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'administrador');
    escolaFiltroFixture($empresa);

    $this->actingAs($user)->get('/admin/aluno')
        ->assertOk()
        ->assertSee('name="escola_id_', false);
});

test('filtro de escola enviado com contexto ativo nao grava na sessao', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'administrador');
    ['escolaA' => $escolaA, 'escolaB' => $escolaB] = escolaFiltroFixture($empresa);

    $this->actingAs($user)->post('/admin/escola-contexto', ['escola_id' => $escolaA->id]);

    $this->actingAs($user)->get('/admin/aluno/filtro?'.http_build_query([
        'escola_id_where' => $escolaB->id,
        'nome_like' => 'Filtro',
    ]));

    expect(session('aluno.filtro.escola_id'))->toBeNull();
    expect(session('aluno.filtro.nome'))->not->toBeNull();
});

test('filtro de escola defasado na sessao e ignorado com contexto ativo', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'administrador');
    ['escolaA' => $escolaA, 'escolaB' => $escolaB] = escolaFiltroFixture($empresa);

    $this->actingAs($user)->post('/admin/escola-contexto', ['escola_id' => $escolaA->id]);

    // Simula filtro gravado antes da troca de contexto: aponta para a
    // escola B, divergente da escola A ativa.
    session(['aluno.filtro.escola_id' => [
        'name' => 'escola_id', 'valor' => $escolaB->id,
        'typeWhere' => 'where', 'nomeExibir' => 'Escola',
    ]]);

    $this->actingAs($user)->get('/admin/aluno')
        ->assertOk()
        ->assertSee('Aluno Filtro A')
        ->assertDontSee('Aluno Filtro B');
});

test('dre esconde o select de escola e ignora escola_id divergente', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'administrador');
    ['escolaA' => $escolaA, 'escolaB' => $escolaB] = escolaFiltroFixture($empresa);

    // O select do navbar usa value="0"; os filtros usam value="".
    $this->actingAs($user)->get('/admin/dre?escola_id='.$escolaB->id)
        ->assertOk()
        ->assertSee('value="">Todas as escolas', false);

    $this->actingAs($user)->post('/admin/escola-contexto', ['escola_id' => $escolaA->id]);

    $this->actingAs($user)->get('/admin/dre?escola_id='.$escolaB->id)
        ->assertOk()
        ->assertDontSee('value="">Todas as escolas', false);
});

test('relatorio de aproveitamento forca a escola do contexto', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'administrador');
    ['escolaA' => $escolaA, 'escolaB' => $escolaB] = escolaFiltroFixture($empresa);

    $this->actingAs($user)->post('/admin/escola-contexto', ['escola_id' => $escolaA->id]);

    // Pede explicitamente a escola B — o relatorio (DB::table, sem
    // escopo global) deve ignorar e usar a escola A do contexto.
    $this->actingAs($user)->get('/admin/relatorio-aproveitamento?escola_id='.$escolaB->id)
        ->assertOk()
        ->assertSee('Turma Filtro A')
        ->assertDontSee('Turma Filtro B');
});
