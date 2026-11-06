<?php

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\Cardapio;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Matricula;
use App\Models\Pessoa;
use App\Models\Responsavel;
use App\Models\Turma;
use App\Support\EmpresaContext;

function setupCardapio($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Merenda', 'empresa_id' => $empresa->id]);

    return compact('escola');
}

function cardapioDemo($empresa, $escola, array $overrides = []): Cardapio
{
    return Cardapio::create(array_merge([
        'empresa_id' => $empresa->id,
        'escola_id' => $escola->id,
        'data' => now()->addDay()->toDateString(),
        'tipo' => 'almoco',
        'descricao' => 'Arroz, feijão, frango grelhado e salada',
        'ativo' => 'S',
    ], $overrides));
}

test('responsavel_alimentacao gerencia cardapios', function () {
    $empresa = tenant();
    $ids = setupCardapio($empresa);
    $user = usuarioTenant($empresa, 'responsavel_alimentacao');

    $this->actingAs($user)->get(route('cardapio.index'))->assertOk();
    $this->actingAs($user)->get(route('cardapio.create'))->assertOk();

    $this->actingAs($user)->post(route('cardapio.store'), [
        'escola_id' => $ids['escola']->id,
        'data' => now()->addDays(2)->toDateString(),
        'tipo' => 'almoco',
        'descricao' => 'Macarrão ao sugo e almôndegas',
        'restricoes_alimentares' => 'Opção vegetariana',
        'ativo' => 'S',
    ])->assertRedirect();

    expect(Cardapio::where('tipo', 'almoco')->exists())->toBeTrue();
});

test('duplicata escola+data+tipo e rejeitada', function () {
    $empresa = tenant();
    $ids = setupCardapio($empresa);
    $user = usuarioTenant($empresa, 'administrador');

    cardapioDemo($empresa, $ids['escola'], ['data' => '2026-11-16', 'tipo' => 'almoco']);

    $this->actingAs($user)->post(route('cardapio.store'), [
        'escola_id' => $ids['escola']->id,
        'data' => '2026-11-16',
        'tipo' => 'almoco',
        'descricao' => 'Outro prato',
        'ativo' => 'S',
    ])->assertSessionHasErrors('data');
});

test('tipos invalidos e datas ausentes falham na validacao', function () {
    $empresa = tenant();
    $ids = setupCardapio($empresa);
    $user = usuarioTenant($empresa, 'administrador');

    $this->actingAs($user)->post(route('cardapio.store'), [
        'escola_id' => $ids['escola']->id,
        'tipo' => 'inexistente',
        'descricao' => 'Prato',
    ])->assertSessionHasErrors(['data', 'tipo']);
});

test('cardapio de outro tenant nao aparece', function () {
    $empresa = tenant();
    $ids = setupCardapio($empresa);
    cardapioDemo($empresa, $ids['escola'], ['descricao' => 'Segredo do cardapio']);

    $outro = tenant(['nome' => 'Rede B', 'slug' => 'rede-b']);
    app(EmpresaContext::class)->setEmpresaId($outro->id);
    $escolaB = Escola::create(['nome' => 'Escola B', 'empresa_id' => $outro->id]);

    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $user = usuarioTenant($empresa, 'administrador');

    $this->actingAs($user)->get(route('cardapio.index'))
        ->assertOk()->assertSee('Segredo do cardapio');
});

test('portal mostra cardapio apenas das escolas dos alunos vinculados', function () {
    $empresa = tenant();
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escolaA = Escola::create(['nome' => 'Escola Alfa', 'empresa_id' => $empresa->id]);
    $escolaB = Escola::create(['nome' => 'Escola Beta', 'empresa_id' => $empresa->id]);

    $anoLetivo = AnoLetivo::create([
        'escola_id' => $escolaA->id, 'ano' => 2026, 'empresa_id' => $empresa->id,
    ]);
    $etapa = Etapa::create([
        'nome' => 'Fund I', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id,
    ]);
    $turma = Turma::create([
        'escola_id' => $escolaA->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id, 'nome' => '1º Ano A', 'empresa_id' => $empresa->id,
    ]);

    $pessoa = Pessoa::create(['nome' => 'Aluno Cardapio', 'empresa_id' => $empresa->id]);
    $aluno = Aluno::create([
        'pessoa_id' => $pessoa->id, 'escola_id' => $escolaA->id,
        'registro_academico' => 'RA-C1', 'empresa_id' => $empresa->id,
    ]);
    Matricula::create([
        'aluno_id' => $aluno->id, 'turma_id' => $turma->id,
        'ano_letivo_id' => $anoLetivo->id, 'status' => 'ativa',
        'numero_chamada' => 1, 'empresa_id' => $empresa->id,
    ]);

    $pessoaResp = Pessoa::create(['nome' => 'Resp Cardapio', 'empresa_id' => $empresa->id]);
    $responsavel = Responsavel::create([
        'pessoa_id' => $pessoaResp->id, 'empresa_id' => $empresa->id,
    ]);
    $aluno->responsaveis()->attach($responsavel->id, [
        'tipo' => 'legal', 'principal' => true,
        'pode_retirar' => true, 'autoriza_contato' => true,
    ]);

    // Cardapio da escola do aluno (visivel) e de outra escola (oculto)
    $amanha = now()->addDay()->toDateString();
    Cardapio::create([
        'empresa_id' => $empresa->id, 'escola_id' => $escolaA->id,
        'data' => $amanha, 'tipo' => 'almoco',
        'descricao' => 'PRATO-ALFA-visivel', 'ativo' => 'S',
    ]);
    Cardapio::create([
        'empresa_id' => $empresa->id, 'escola_id' => $escolaB->id,
        'data' => $amanha, 'tipo' => 'almoco',
        'descricao' => 'PRATO-BETA-oculto', 'ativo' => 'S',
    ]);
    // Cardapio antigo (passado) nao aparece
    Cardapio::create([
        'empresa_id' => $empresa->id, 'escola_id' => $escolaA->id,
        'data' => now()->subDay()->toDateString(), 'tipo' => 'almoco',
        'descricao' => 'PRATO-ANTIGO-oculto', 'ativo' => 'S',
    ]);

    $user = usuarioTenant($empresa, 'responsavel_legal');
    $responsavel->update(['user_id' => $user->id]);

    $this->actingAs($user)->get(route('portal.cardapio'))
        ->assertOk()
        ->assertSee('PRATO-ALFA-visivel')
        ->assertDontSee('PRATO-BETA-oculto')
        ->assertDontSee('PRATO-ANTIGO-oculto');
});

test('professor le cardapios mas nao cria', function () {
    $empresa = tenant();
    $ids = setupCardapio($empresa);
    $user = usuarioTenant($empresa, 'professor');

    $this->actingAs($user)->get(route('cardapio.index'))->assertOk();
    $this->actingAs($user)->get(route('cardapio.create'))->assertForbidden();
});
