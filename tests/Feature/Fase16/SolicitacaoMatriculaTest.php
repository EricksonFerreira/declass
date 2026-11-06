<?php

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Matricula;
use App\Models\SolicitacaoMatricula;
use App\Models\Turma;
use App\Support\EmpresaContext;

function setupSolicitacao($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Matriz', 'empresa_id' => $empresa->id]);
    $anoLetivo = AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => 2026, 'empresa_id' => $empresa->id,
    ]);
    $etapa = Etapa::create([
        'nome' => 'Fundamental', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id,
    ]);
    $turma = Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id, 'nome' => '1º Ano A', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);

    return compact('escola', 'anoLetivo', 'etapa', 'turma');
}

function dadosSolicitacao(array $ids, array $extra = []): array
{
    return array_merge([
        'escola_id' => $ids['escola']->id,
        'ano_letivo_id' => $ids['anoLetivo']->id,
        'etapa_id' => $ids['etapa']->id,
        'nome_aluno' => 'Pedro Solicitante',
        'data_nascimento_aluno' => '2019-05-10',
        'sexo_aluno' => 'masculino',
        'nome_responsavel' => 'Maria Solicitante',
        'parentesco' => 'mae',
        'telefone_responsavel' => '11999990000',
        'email_responsavel' => 'maria@teste.com',
    ], $extra);
}

test('secretaria registra solicitacao e ela aparece na fila', function () {
    $empresa = tenant();
    $ids = setupSolicitacao($empresa);
    $secretaria = usuarioTenant($empresa, 'secretario');

    $this->actingAs($secretaria)
        ->get(route('solicitacao_matricula.index'))
        ->assertOk();

    $this->actingAs($secretaria)
        ->post(route('solicitacao_matricula.store'), dadosSolicitacao($ids))
        ->assertRedirect();

    $this->assertDatabaseHas('solicitacoes_matricula', [
        'nome_aluno' => 'Pedro Solicitante',
        'status' => 'pendente',
        'empresa_id' => $empresa->id,
    ]);
});

test('aprovacao gera aluno matricula e responsavel em transacao', function () {
    $empresa = tenant();
    $ids = setupSolicitacao($empresa);
    $secretaria = usuarioTenant($empresa, 'secretario');

    $this->actingAs($secretaria)
        ->post(route('solicitacao_matricula.store'), dadosSolicitacao($ids));

    $solicitacao = SolicitacaoMatricula::first();

    $this->actingAs($secretaria)
        ->post(route('solicitacao_matricula.aprovar', $solicitacao->id), [
            'turma_id' => $ids['turma']->id,
        ])
        ->assertRedirect(route('solicitacao_matricula.show', $solicitacao->id));

    $solicitacao->refresh();
    expect($solicitacao->status)->toBe('aprovada')
        ->and($solicitacao->aluno_id)->not->toBeNull()
        ->and($solicitacao->matricula_id)->not->toBeNull()
        ->and($solicitacao->responsavel_id)->not->toBeNull()
        ->and($solicitacao->analisado_por)->toBe($secretaria->id);

    $matricula = Matricula::find($solicitacao->matricula_id);
    expect($matricula->status)->toBe('ativa')
        ->and($matricula->turma_id)->toBe($ids['turma']->id)
        ->and($matricula->numero_chamada)->toBe(1);

    // responsavel legal vinculado ao aluno
    $this->assertDatabaseHas('aluno_responsavel', [
        'aluno_id' => $solicitacao->aluno_id,
        'responsavel_id' => $solicitacao->responsavel_id,
        'tipo' => 'legal',
        'principal' => true,
    ]);
});

test('aprovacao exige turma da mesma escola', function () {
    $empresa = tenant();
    $ids = setupSolicitacao($empresa);
    $secretaria = usuarioTenant($empresa, 'secretario');

    // segunda escola + turma
    $escola2 = Escola::create(['nome' => 'Escola 2', 'empresa_id' => $empresa->id]);
    $ano2 = AnoLetivo::create(['escola_id' => $escola2->id, 'ano' => 2026, 'empresa_id' => $empresa->id]);
    $turma2 = Turma::create([
        'escola_id' => $escola2->id, 'ano_letivo_id' => $ano2->id,
        'etapa_id' => $ids['etapa']->id, 'nome' => '1º Ano B', 'turno' => 'vespertino',
        'empresa_id' => $empresa->id,
    ]);

    $this->actingAs($secretaria)
        ->post(route('solicitacao_matricula.store'), dadosSolicitacao($ids));
    $solicitacao = SolicitacaoMatricula::first();

    $this->actingAs($secretaria)
        ->post(route('solicitacao_matricula.aprovar', $solicitacao->id), [
            'turma_id' => $turma2->id,
        ])
        ->assertSessionHasErrors();

    expect($solicitacao->fresh()->status)->toBe('pendente');
});

test('recusa exige motivo e marca status', function () {
    $empresa = tenant();
    $ids = setupSolicitacao($empresa);
    $secretaria = usuarioTenant($empresa, 'secretario');

    $this->actingAs($secretaria)
        ->post(route('solicitacao_matricula.store'), dadosSolicitacao($ids));
    $solicitacao = SolicitacaoMatricula::first();

    // sem motivo: erro de validacao
    $this->actingAs($secretaria)
        ->post(route('solicitacao_matricula.recusar', $solicitacao->id), ['motivo_recusa' => ''])
        ->assertSessionHasErrors('motivo_recusa');

    $this->actingAs($secretaria)
        ->post(route('solicitacao_matricula.recusar', $solicitacao->id), [
            'motivo_recusa' => 'Documentação incompleta',
        ]);

    $solicitacao->refresh();
    expect($solicitacao->status)->toBe('recusada')
        ->and($solicitacao->motivo_recusa)->toBe('Documentação incompleta')
        ->and($solicitacao->aluno_id)->toBeNull();
});

test('solicitacao analisada nao pode ser editada nem re-analisada', function () {
    $empresa = tenant();
    $ids = setupSolicitacao($empresa);
    $secretaria = usuarioTenant($empresa, 'secretario');

    $this->actingAs($secretaria)
        ->post(route('solicitacao_matricula.store'), dadosSolicitacao($ids));
    $solicitacao = SolicitacaoMatricula::first();

    $this->actingAs($secretaria)
        ->post(route('solicitacao_matricula.aprovar', $solicitacao->id), ['turma_id' => $ids['turma']->id]);

    // segunda analise bloqueada
    $this->actingAs($secretaria)
        ->post(route('solicitacao_matricula.aprovar', $solicitacao->id), ['turma_id' => $ids['turma']->id])
        ->assertSessionHasErrors();

    // edicao bloqueada (403 via service abort)
    $this->actingAs($secretaria)
        ->put(route('solicitacao_matricula.update', $solicitacao->id), dadosSolicitacao($ids))
        ->assertForbidden();
});

test('professor nao acessa a fila de matriculas', function () {
    $empresa = tenant();
    setupSolicitacao($empresa);
    $professor = usuarioTenant($empresa, 'professor');

    $this->actingAs($professor)
        ->get(route('solicitacao_matricula.index'))
        ->assertForbidden();
});

test('solicitacoes tem escopo de tenant', function () {
    $empresa = tenant();
    $ids = setupSolicitacao($empresa);
    $secretaria = usuarioTenant($empresa, 'secretario');

    $this->actingAs($secretaria)
        ->post(route('solicitacao_matricula.store'), dadosSolicitacao($ids));

    $outro = tenant(['nome' => 'Rede Mat B', 'slug' => 'rede-mat-b']);
    app(EmpresaContext::class)->setEmpresaId($outro->id);
    $secretariaB = usuarioTenant($outro, 'secretario');

    $this->actingAs($secretariaB)
        ->get(route('solicitacao_matricula.index'))
        ->assertOk()
        ->assertDontSee('Pedro Solicitante');
});
