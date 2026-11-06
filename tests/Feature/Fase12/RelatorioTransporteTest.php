<?php

use App\Models\ParadaTransporte;
use App\Models\RotaTransporte;
use App\Models\TransporteAluno;
use App\Models\Veiculo;
use App\Support\EmpresaContext;

test('relatorio mostra ocupacao das rotas com resumo', function () {
    $empresa = tenant();
    $ids = setupTransporte($empresa);

    // 19 passageiros em veiculo de 20 -> 95% (lotada)
    for ($i = 0; $i < 19; $i++) {
        $aluno = alunoExtra($empresa, $ids['escola'], 'RA-O'.str_pad((string) $i, 3, '0', STR_PAD_LEFT));
        TransporteAluno::create([
            'empresa_id' => $empresa->id, 'aluno_id' => $aluno->id,
            'rota_id' => $ids['rota']->id, 'parada_id' => $ids['parada']->id, 'ativo' => 'S',
        ]);
    }

    // Rota sem veiculo -> situacao sem_veiculo
    $rotaSemVeiculo = RotaTransporte::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'veiculo_id' => null, 'nome' => 'Rota Rural', 'ativo' => 'S',
    ]);
    $parada2 = ParadaTransporte::create([
        'empresa_id' => $empresa->id, 'rota_id' => $rotaSemVeiculo->id,
        'nome' => 'Ponto Sítio', 'ordem' => 1,
    ]);
    $aluno2 = alunoExtra($empresa, $ids['escola'], 'RA-SV1');
    TransporteAluno::create([
        'empresa_id' => $empresa->id, 'aluno_id' => $aluno2->id,
        'rota_id' => $rotaSemVeiculo->id, 'parada_id' => $parada2->id, 'ativo' => 'S',
    ]);

    $user = usuarioTenant($empresa, 'responsavel_transporte');

    $response = $this->actingAs($user)->get(route('relatorio_transporte.index'));
    $response->assertOk()
        ->assertSee('Rota Centro')
        ->assertSee('Rota Rural');

    $resumo = $response->viewData('resumo');
    expect($resumo['rotas'])->toBe(2)
        ->and($resumo['passageiros'])->toBe(20)
        ->and($resumo['lotadas'])->toBe(1)
        ->and($resumo['sem_veiculo'])->toBe(1);

    // Filtro por situacao
    $response = $this->actingAs($user)
        ->get(route('relatorio_transporte.index', ['situacao' => 'sem_veiculo']));
    $response->assertOk()
        ->assertSee('Rota Rural')
        ->assertDontSee('Rota Centro');
});

test('veiculo superlotado e marcado corretamente', function () {
    $empresa = tenant();
    $ids = setupTransporte($empresa);

    // capacidade 20, 21 passageiros -> 105% superlotada
    $veiculo = Veiculo::where('id', $ids['veiculo']->id)->update(['capacidade' => 20]);

    for ($i = 0; $i < 21; $i++) {
        $aluno = alunoExtra($empresa, $ids['escola'], 'RA-S'.str_pad((string) $i, 3, '0', STR_PAD_LEFT));
        TransporteAluno::create([
            'empresa_id' => $empresa->id, 'aluno_id' => $aluno->id,
            'rota_id' => $ids['rota']->id, 'parada_id' => $ids['parada']->id, 'ativo' => 'S',
        ]);
    }

    $user = usuarioTenant($empresa, 'administrador');
    $response = $this->actingAs($user)->get(route('relatorio_transporte.index'));
    $response->assertOk()->assertSee('Superlotada');

    $linhas = $response->viewData('linhas');
    expect($linhas->firstWhere('rota.id', $ids['rota']->id)['situacao'])->toBe('superlotada');
});

test('professor nao acessa relatorio de transporte', function () {
    $empresa = tenant();
    setupTransporte($empresa);

    $this->actingAs(usuarioTenant($empresa, 'professor'))
        ->get(route('relatorio_transporte.index'))
        ->assertForbidden();
});

test('relatorio de transporte tem escopo de tenant', function () {
    $empresa = tenant();
    $ids = setupTransporte($empresa);
    $aluno = alunoExtra($empresa, $ids['escola'], 'RA-TZ1');
    TransporteAluno::create([
        'empresa_id' => $empresa->id, 'aluno_id' => $aluno->id,
        'rota_id' => $ids['rota']->id, 'parada_id' => $ids['parada']->id, 'ativo' => 'S',
    ]);

    $outro = tenant(['nome' => 'Rede T', 'slug' => 'rede-t']);
    app(EmpresaContext::class)->setEmpresaId($outro->id);

    $response = $this->actingAs(usuarioTenant($outro, 'administrador'))
        ->get(route('relatorio_transporte.index'));
    $response->assertOk();
    expect($response->viewData('resumo')['rotas'])->toBe(0);
});
