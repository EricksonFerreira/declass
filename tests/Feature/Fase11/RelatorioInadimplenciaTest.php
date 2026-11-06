<?php

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Matricula;
use App\Models\Mensalidade;
use App\Models\Pessoa;
use App\Models\Turma;
use App\Support\EmpresaContext;

function setupInadimplencia($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Fin', 'empresa_id' => $empresa->id]);
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

    $matriculas = collect();
    foreach (['Davi Alves', 'Elisa Prado', 'Fabio Nunes'] as $i => $nome) {
        $pessoa = Pessoa::create(['nome' => $nome, 'empresa_id' => $empresa->id]);
        $aluno = Aluno::create([
            'pessoa_id' => $pessoa->id, 'escola_id' => $escola->id,
            'registro_academico' => 'RA-F'.($i + 1), 'empresa_id' => $empresa->id,
        ]);
        $matriculas->push(Matricula::create([
            'aluno_id' => $aluno->id, 'turma_id' => $turma->id,
            'ano_letivo_id' => $anoLetivo->id, 'status' => 'ativa',
            'numero_chamada' => $i + 1, 'empresa_id' => $empresa->id,
        ]));
    }

    $ontem = now()->subDay()->toDateString();
    $amanha = now()->addDay()->toDateString();

    // Davi: vencida (pendente + vencimento passado)
    Mensalidade::create([
        'empresa_id' => $empresa->id, 'matricula_id' => $matriculas[0]->id,
        'competencia' => '2026-01', 'valor' => 500, 'vencimento' => $ontem,
        'status' => 'pendente',
    ]);
    // Elisa: a vencer (pendente + vencimento futuro)
    Mensalidade::create([
        'empresa_id' => $empresa->id, 'matricula_id' => $matriculas[1]->id,
        'competencia' => '2026-02', 'valor' => 600, 'vencimento' => $amanha,
        'status' => 'pendente',
    ]);
    // Fabio: paga
    Mensalidade::create([
        'empresa_id' => $empresa->id, 'matricula_id' => $matriculas[2]->id,
        'competencia' => '2026-01', 'valor' => 700, 'vencimento' => $ontem,
        'status' => 'paga', 'data_pagamento' => $ontem, 'forma_pagamento' => 'pix',
    ]);

    return compact('escola', 'turma', 'matriculas');
}

test('financeiro acessa relatorio de inadimplencia com resumo', function () {
    $empresa = tenant();
    setupInadimplencia($empresa);
    $user = usuarioTenant($empresa, 'financeiro');

    $response = $this->actingAs($user)->get(route('relatorio_inadimplencia.index'));
    $response->assertOk()
        ->assertSee('Davi Alves')
        ->assertSee('Elisa Prado')
        ->assertSee('Fabio Nunes');

    $resumo = $response->viewData('resumo');
    expect($resumo['total'])->toBe(3)
        ->and($resumo['vencidas'])->toBe(1)
        ->and($resumo['a_vencer'])->toBe(1)
        ->and($resumo['pagas'])->toBe(1)
        ->and($resumo['valor_aberto'])->toBe(1100.0)
        ->and($resumo['valor_vencido'])->toBe(500.0)
        ->and($resumo['valor_pago'])->toBe(700.0);
});

test('filtro por situacao vencida retorna apenas dividas em atraso', function () {
    $empresa = tenant();
    setupInadimplencia($empresa);
    $user = usuarioTenant($empresa, 'administrador');

    $response = $this->actingAs($user)
        ->get(route('relatorio_inadimplencia.index', ['situacao' => 'vencida']));

    $response->assertOk()
        ->assertSee('Davi Alves')
        ->assertDontSee('Elisa Prado')
        ->assertDontSee('Fabio Nunes');
});

test('filtro por competencia restringe o relatorio', function () {
    $empresa = tenant();
    setupInadimplencia($empresa);
    $user = usuarioTenant($empresa, 'administrador');

    $response = $this->actingAs($user)
        ->get(route('relatorio_inadimplencia.index', ['competencia' => '2026-02']));

    $response->assertOk()
        ->assertSee('Elisa Prado')
        ->assertDontSee('Davi Alves');
});

test('professor e coordenador nao acessam relatorio financeiro', function () {
    $empresa = tenant();
    setupInadimplencia($empresa);

    $this->actingAs(usuarioTenant($empresa, 'professor'))
        ->get(route('relatorio_inadimplencia.index'))
        ->assertForbidden();

    $this->actingAs(usuarioTenant($empresa, 'coordenador_pedagogico'))
        ->get(route('relatorio_inadimplencia.index'))
        ->assertForbidden();
});

test('relatorio tem escopo de tenant', function () {
    $empresa = tenant();
    setupInadimplencia($empresa);

    $outro = tenant(['nome' => 'Rede F', 'slug' => 'rede-f']);
    app(EmpresaContext::class)->setEmpresaId($outro->id);

    $user = usuarioTenant($outro, 'administrador');
    $response = $this->actingAs($user)->get(route('relatorio_inadimplencia.index'));
    $response->assertOk();
    expect($response->viewData('resumo')['total'])->toBe(0);
});
