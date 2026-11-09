<?php

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\ContaReceber;
use App\Models\Despesa;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Matricula;
use App\Models\Mensalidade;
use App\Models\Pessoa;
use App\Models\Turma;
use App\Support\EmpresaContext;

function setupVisaoGeral($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $ano = (int) date('Y');

    $escola = Escola::create(['nome' => 'Escola Visão', 'empresa_id' => $empresa->id]);
    $anoLetivo = AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => $ano, 'empresa_id' => $empresa->id,
    ]);
    $etapa = Etapa::create([
        'nome' => '7º Ano EF', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id,
    ]);
    $turma = Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id, 'nome' => '7º Ano A', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);

    $pessoa = Pessoa::create(['nome' => 'Marcos Lima', 'empresa_id' => $empresa->id]);
    $aluno = Aluno::create([
        'pessoa_id' => $pessoa->id, 'escola_id' => $escola->id,
        'registro_academico' => 'RA-VG1', 'empresa_id' => $empresa->id,
    ]);
    $matricula = Matricula::create([
        'aluno_id' => $aluno->id, 'turma_id' => $turma->id,
        'ano_letivo_id' => $anoLetivo->id, 'status' => 'ativa',
        'numero_chamada' => 1, 'empresa_id' => $empresa->id,
    ]);

    // Vencida em aberto (inadimplencia) + a vencer + paga
    Mensalidade::create([
        'empresa_id' => $empresa->id, 'escola_id' => $escola->id,
        'matricula_id' => $matricula->id, 'competencia' => $ano.'-01',
        'valor' => 500, 'vencimento' => now()->subDays(10)->toDateString(), 'status' => 'pendente',
    ]);
    Mensalidade::create([
        'empresa_id' => $empresa->id, 'escola_id' => $escola->id,
        'matricula_id' => $matricula->id, 'competencia' => $ano.'-12',
        'valor' => 600, 'vencimento' => now()->addDays(30)->toDateString(), 'status' => 'pendente',
    ]);
    Mensalidade::create([
        'empresa_id' => $empresa->id, 'escola_id' => $escola->id,
        'matricula_id' => $matricula->id, 'competencia' => $ano.'-02',
        'valor' => 700, 'vencimento' => now()->subDays(5)->toDateString(),
        'status' => 'paga', 'data_pagamento' => now()->toDateString(),
    ]);

    Despesa::create([
        'empresa_id' => $empresa->id, 'escola_id' => $escola->id,
        'fornecedor' => 'Papelaria X', 'descricao' => 'Material', 'categoria' => 'material',
        'valor' => 1200, 'data_vencimento' => now()->toDateString(),
        'data_pagamento' => now()->toDateString(), 'status' => 'paga',
    ]);
    ContaReceber::create([
        'empresa_id' => $empresa->id, 'escola_id' => $escola->id,
        'descricao' => 'Taxa de matricula', 'categoria' => 'taxas',
        'valor' => 300, 'data_vencimento' => now()->addDays(10)->toDateString(),
        'status' => 'prevista',
    ]);

    return compact('escola', 'turma', 'matricula', 'ano');
}

test('financeiro acessa a visao geral com KPIs e inadimplencia por turma', function () {
    $empresa = tenant();
    setupVisaoGeral($empresa);
    $user = usuarioTenant($empresa, 'financeiro');

    $response = $this->actingAs($user)->get(route('financeiro_visao_geral.index'));

    $response->assertOk()
        ->assertSee('Visão Geral do Financeiro')
        ->assertSee('7º Ano A')
        ->assertSee('Marcos Lima')
        ->assertSee('Saídas no ano')
        ->assertSee('Receita Prevista');

    $dados = $response->viewData('dados');
    expect($dados['inadimplencia_total'])->toBe(500.0)
        ->and($dados['saidas_ano'])->toBe(1200.0)
        ->and($dados['recebido_hoje'])->toBe(700.0)
        ->and($dados['a_receber_ano'])->toBe(1400.0)
        ->and($dados['por_turma']->first()->nome)->toBe('7º Ano A');
});

test('filtro por escola isola os numeros', function () {
    $empresa = tenant();
    $ctx = setupVisaoGeral($empresa);
    $outra = Escola::create(['nome' => 'Outra Escola', 'empresa_id' => $empresa->id]);
    $user = usuarioTenant($empresa, 'administrador');

    $response = $this->actingAs($user)
        ->get(route('financeiro_visao_geral.index', ['escola_id' => $outra->id]));

    $response->assertOk();
    $dados = $response->viewData('dados');
    expect($dados['inadimplencia_total'])->toBe(0.0)
        ->and($dados['saidas_ano'])->toBe(0.0);
});

test('usuario sem permissao nao acessa a visao geral', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'professor');

    $this->actingAs($user)
        ->get(route('financeiro_visao_geral.index'))
        ->assertForbidden();
});
