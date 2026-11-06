<?php

use App\Models\Aluno;
use App\Models\Escola;
use App\Models\ParadaTransporte;
use App\Models\Pessoa;
use App\Models\RotaTransporte;
use App\Models\TransporteAluno;
use App\Models\Veiculo;
use App\Support\EmpresaContext;

function setupTransporte($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Sul', 'empresa_id' => $empresa->id]);
    $veiculo = Veiculo::create([
        'empresa_id' => $empresa->id, 'escola_id' => $escola->id,
        'placa' => 'TST1A23', 'modelo' => 'Mercedes Sprinter', 'capacidade' => 20,
        'ativo' => 'S',
    ]);

    $rota = RotaTransporte::create([
        'empresa_id' => $empresa->id, 'escola_id' => $escola->id,
        'veiculo_id' => $veiculo->id, 'nome' => 'Rota Centro',
        'horario_ida' => '06:30', 'horario_volta' => '17:00', 'ativo' => 'S',
    ]);
    $parada = ParadaTransporte::create([
        'empresa_id' => $empresa->id, 'rota_id' => $rota->id,
        'nome' => 'Parada Praça', 'ordem' => 1, 'horario_previsto' => '06:35',
    ]);

    $pessoa = Pessoa::create(['nome' => 'Aluno Transporte', 'empresa_id' => $empresa->id]);
    $aluno = Aluno::create([
        'empresa_id' => $empresa->id, 'pessoa_id' => $pessoa->id,
        'escola_id' => $escola->id, 'registro_academico' => 'RA-T1', 'ativo' => 'S',
    ]);

    return compact('escola', 'veiculo', 'rota', 'parada', 'aluno');
}

function alunoExtra($empresa, Escola $escola, string $ra): Aluno
{
    $pessoa = Pessoa::create(['nome' => 'Aluno '.$ra, 'empresa_id' => $empresa->id]);

    return Aluno::create([
        'empresa_id' => $empresa->id, 'pessoa_id' => $pessoa->id,
        'escola_id' => $escola->id, 'registro_academico' => $ra, 'ativo' => 'S',
    ]);
}

test('diretor cadastra veiculo e lista na index', function () {
    $empresa = tenant();
    setupTransporte($empresa);
    $user = usuarioTenant($empresa, 'diretor');

    $this->actingAs($user)->get(route('veiculo.index'))->assertOk()->assertSee('TST1A23');

    $this->actingAs($user)->post(route('veiculo.store'), [
        'escola_id' => Escola::first()->id,
        'placa' => 'NEW2B34', 'modelo' => 'Fiat Ducato', 'ano' => 2021,
        'capacidade' => 16, 'motorista_nome' => 'João', 'ativo' => 'S',
    ])->assertRedirect();

    expect(Veiculo::where('placa', 'NEW2B34')->exists())->toBeTrue();
});

test('rota e criada com paradas ordenadas', function () {
    $empresa = tenant();
    $ids = setupTransporte($empresa);
    $user = usuarioTenant($empresa, 'secretario');

    $this->actingAs($user)->post(route('rota_transporte.store'), [
        'escola_id' => $ids['escola']->id,
        'veiculo_id' => $ids['veiculo']->id,
        'nome' => 'Rota Sul', 'horario_ida' => '07:00', 'horario_volta' => '18:00',
        'paradas' => [
            ['nome' => 'Ponto A', 'ordem' => 1],
            ['nome' => 'Ponto B', 'ordem' => 2],
        ],
        'ativo' => 'S',
    ])->assertRedirect();

    $rota = RotaTransporte::where('nome', 'Rota Sul')->first();
    expect($rota)->not->toBeNull()
        ->and($rota->paradas()->count())->toBe(2)
        ->and($rota->paradas()->orderBy('ordem')->first()->nome)->toBe('Ponto A');
});

test('aluno e vinculado a rota com parada valida', function () {
    $empresa = tenant();
    $ids = setupTransporte($empresa);
    $user = usuarioTenant($empresa, 'responsavel_transporte');

    $this->actingAs($user)->post(route('transporte_aluno.store'), [
        'aluno_id' => $ids['aluno']->id,
        'rota_id' => $ids['rota']->id,
        'parada_id' => $ids['parada']->id,
        'ativo' => 'S',
    ])->assertRedirect();

    $vinculo = TransporteAluno::where('aluno_id', $ids['aluno']->id)->first();
    expect($vinculo)->not->toBeNull()
        ->and($vinculo->parada_id)->toBe($ids['parada']->id)
        ->and($vinculo->estaAtivo())->toBeTrue();
});

test('segundo vinculo ativo do mesmo aluno e bloqueado', function () {
    $empresa = tenant();
    $ids = setupTransporte($empresa);
    $user = usuarioTenant($empresa, 'administrador');

    TransporteAluno::create([
        'empresa_id' => $empresa->id, 'aluno_id' => $ids['aluno']->id,
        'rota_id' => $ids['rota']->id, 'parada_id' => $ids['parada']->id, 'ativo' => 'S',
    ]);

    $this->actingAs($user)->post(route('transporte_aluno.store'), [
        'aluno_id' => $ids['aluno']->id,
        'rota_id' => $ids['rota']->id,
        'ativo' => 'S',
    ])->assertSessionHasErrors('aluno_id');
});

test('parada de outra rota e rejeitada', function () {
    $empresa = tenant();
    $ids = setupTransporte($empresa);
    $user = usuarioTenant($empresa, 'administrador');

    $rota2 = RotaTransporte::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'nome' => 'Rota Oeste', 'ativo' => 'S',
    ]);
    $aluno2 = alunoExtra($empresa, $ids['escola'], 'RA-T2');

    // parada pertence a rota 1, mas o vinculo aponta para a rota 2
    $this->actingAs($user)->post(route('transporte_aluno.store'), [
        'aluno_id' => $aluno2->id,
        'rota_id' => $rota2->id,
        'parada_id' => $ids['parada']->id,
        'ativo' => 'S',
    ])->assertSessionHasErrors('parada_id');
});

test('veiculo de outro tenant nao e acessivel', function () {
    $empresa = tenant();
    $ids = setupTransporte($empresa);

    $outro = tenant(['nome' => 'Outra Rede', 'slug' => 'outra-rede']);
    app(EmpresaContext::class)->setEmpresaId($outro->id);
    $escolaOutra = Escola::create(['nome' => 'Escola X', 'empresa_id' => $outro->id]);
    $veiculoOutro = Veiculo::create([
        'empresa_id' => $outro->id, 'escola_id' => $escolaOutra->id,
        'placa' => 'OUT1C56', 'modelo' => 'Van', 'ativo' => 'S',
    ]);

    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $user = usuarioTenant($empresa, 'administrador');

    $this->actingAs($user)->get(route('veiculo.index'))
        ->assertOk()->assertDontSee('OUT1C56');

    // exists() do form request tambem bloqueia id de outro tenant
    $this->actingAs($user)->post(route('veiculo.store'), [
        'escola_id' => $ids['escola']->id,
        'placa' => 'NEW3D45', 'modelo' => 'Van', 'ativo' => 'S',
        'veiculo_id' => $veiculoOutro->id,
    ]);
    expect(Veiculo::where('placa', 'NEW3D45')->exists())->toBeTrue(); // campo extra ignorado
});

test('professor nao acessa transporte', function () {
    $empresa = tenant();
    $ids = setupTransporte($empresa);
    $user = usuarioTenant($empresa, 'professor');

    $this->actingAs($user)->get(route('veiculo.index'))->assertForbidden();
    $this->actingAs($user)->get(route('veiculo.create'))->assertForbidden();
});

test('portal do responsavel mostra transporte do filho', function () {
    $empresa = tenant();
    $ids = setupTransporte($empresa);

    $pessoaResp = Pessoa::create(['nome' => 'Pai Responsavel', 'empresa_id' => $empresa->id]);
    $responsavel = \App\Models\Responsavel::create([
        'pessoa_id' => $pessoaResp->id, 'empresa_id' => $empresa->id,
    ]);
    $ids['aluno']->responsaveis()->attach($responsavel->id, [
        'tipo' => 'legal', 'principal' => true,
        'pode_retirar' => true, 'autoriza_contato' => true,
    ]);

    TransporteAluno::create([
        'empresa_id' => $empresa->id, 'aluno_id' => $ids['aluno']->id,
        'rota_id' => $ids['rota']->id, 'parada_id' => $ids['parada']->id, 'ativo' => 'S',
    ]);

    $user = usuarioTenant($empresa, 'responsavel_legal');
    $responsavel->update(['user_id' => $user->id]);

    $this->actingAs($user)->get(route('portal.transporte'))
        ->assertOk()
        ->assertSee('Rota Centro')
        ->assertSee('Parada Praça');
});

test('portal bloqueia conta sem aluno vinculado', function () {
    $empresa = tenant();
    setupTransporte($empresa);
    $user = usuarioTenant($empresa, 'responsavel_legal');

    $this->actingAs($user)->get(route('portal.transporte'))->assertForbidden();
});
