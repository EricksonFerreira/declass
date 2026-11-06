<?php

use App\Models\Aluno;
use App\Models\Matricula;
use App\Models\Mensalidade;
use App\Models\Pessoa;
use App\Models\PlanoMensalidade;
use App\Models\Responsavel;
use App\Models\Turma;
use App\Services\MensalidadeService;
use App\Support\EmpresaContext;

function setupFinanceiro($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = \App\Models\Escola::create(['nome' => 'Escola Fin', 'empresa_id' => $empresa->id]);
    $anoLetivo = \App\Models\AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => 2026, 'empresa_id' => $empresa->id,
    ]);
    $etapa = \App\Models\Etapa::create([
        'nome' => 'Fund I', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id,
    ]);
    $turma = Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id, 'nome' => 'Turma Fin', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);

    $plano = PlanoMensalidade::create([
        'empresa_id' => $empresa->id, 'nome' => 'Integral', 'valor' => 800,
    ]);

    $matriculas = [];
    foreach (['Ana', 'Beto'] as $i => $nome) {
        $pessoa = Pessoa::create(['nome' => $nome, 'empresa_id' => $empresa->id]);
        $aluno = Aluno::create([
            'pessoa_id' => $pessoa->id, 'escola_id' => $escola->id,
            'empresa_id' => $empresa->id, 'registro_academico' => 'RA-F'.$i,
        ]);
        $matriculas[] = Matricula::create([
            'aluno_id' => $aluno->id, 'turma_id' => $turma->id,
            'ano_letivo_id' => $anoLetivo->id, 'status' => 'ativa',
            'plano_mensalidade_id' => $plano->id,
            'numero_chamada' => $i + 1, 'empresa_id' => $empresa->id,
        ]);
    }

    return compact('turma', 'plano', 'matriculas');
}

test('plano de mensalidade pode ser criado', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa);

    $this->actingAs($admin)->post(route('plano_mensalidade.store'), [
        'nome' => 'Parcial', 'valor' => 500, 'ativo' => 'S',
    ])->assertRedirect();

    expect(PlanoMensalidade::where('nome', 'Parcial')->exists())->toBeTrue();
});

test('gerar competencia cria mensalidades para matriculas com plano', function () {
    $empresa = tenant();
    usuarioTenant($empresa);
    $ids = setupFinanceiro($empresa);

    $service = app(MensalidadeService::class);
    $resultado = $service->gerarCompetencia('2026-03', '2026-03-10');

    expect($resultado['criadas'])->toBe(2)
        ->and(Mensalidade::count())->toBe(2)
        ->and(Mensalidade::first()->valor)->toBe(800.0)
        ->and(Mensalidade::first()->status)->toBe('pendente');
});

test('gerar competencia ignora duplicatas', function () {
    $empresa = tenant();
    usuarioTenant($empresa);
    setupFinanceiro($empresa);

    $service = app(MensalidadeService::class);
    $service->gerarCompetencia('2026-03', '2026-03-10');
    $segunda = $service->gerarCompetencia('2026-03', '2026-03-10');

    expect($segunda['criadas'])->toBe(0)
        ->and($segunda['ignoradas'])->toBe(2)
        ->and(Mensalidade::count())->toBe(2);
});

test('baixar marca mensalidade como paga', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa);
    setupFinanceiro($empresa);

    app(MensalidadeService::class)->gerarCompetencia('2026-03', '2026-03-10');
    $mensalidade = Mensalidade::first();

    $this->actingAs($admin)->post(route('mensalidade.baixar', $mensalidade->id), [
        'forma_pagamento' => 'pix',
    ])->assertRedirect();

    $mensalidade->refresh();
    expect($mensalidade->status)->toBe('paga')
        ->and($mensalidade->forma_pagamento)->toBe('pix')
        ->and($mensalidade->data_pagamento)->not->toBeNull();
});

test('situacao vencida e derivada de pendente com vencimento passado', function () {
    $empresa = tenant();
    usuarioTenant($empresa);
    $ids = setupFinanceiro($empresa);

    Mensalidade::create([
        'empresa_id' => $empresa->id,
        'matricula_id' => $ids['matriculas'][0]->id,
        'plano_mensalidade_id' => $ids['plano']->id,
        'competencia' => '2026-01',
        'valor' => 800,
        'vencimento' => '2026-01-10',
        'status' => 'pendente',
    ]);

    $m = Mensalidade::first();
    expect($m->situacao)->toBe('vencida');

    $inadimplentes = app(MensalidadeService::class)->inadimplentes();
    expect($inadimplentes)->toHaveCount(1);
});

test('portal financeiro mostra apenas alunos vinculados', function () {
    $empresa = tenant();
    $ids = setupFinanceiro($empresa);

    // Vincula responsavel ao primeiro aluno
    $pessoa = Pessoa::create(['nome' => 'Resp', 'empresa_id' => $empresa->id]);
    $resp = Responsavel::create(['pessoa_id' => $pessoa->id, 'empresa_id' => $empresa->id]);
    $aluno = Aluno::find($ids['matriculas'][0]->aluno_id);
    $aluno->responsaveis()->attach($resp->id, ['tipo' => 'legal']);

    $user = usuarioTenant($empresa, 'responsavel_legal');
    $resp->update(['user_id' => $user->id]);

    app(MensalidadeService::class)->gerarCompetencia('2026-03', '2026-03-10');

    $this->actingAs($user)
        ->get(route('portal.financeiro'))
        ->assertOk()
        ->assertSee($aluno->pessoa->nome)
        ->assertDontSee('Beto');
});

test('perfil financeiro acessa mensalidades e professor nao', function () {
    $empresa = tenant();
    setupFinanceiro($empresa);

    $fin = usuarioTenant($empresa, 'financeiro');
    $this->actingAs($fin)->get(route('mensalidade.index'))->assertOk();

    $prof = usuarioTenant($empresa, 'professor');
    $this->actingAs($prof)->get(route('mensalidade.index'))->assertForbidden();
});
