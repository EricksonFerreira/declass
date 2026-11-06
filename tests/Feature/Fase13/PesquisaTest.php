<?php

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Matricula;
use App\Models\Pesquisa;
use App\Models\PesquisaPergunta;
use App\Models\Pessoa;
use App\Models\Responsavel;
use App\Models\Turma;
use App\Support\EmpresaContext;

function setupPesquisa($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Pesquisa', 'empresa_id' => $empresa->id]);
    $anoLetivo = AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => 2026, 'empresa_id' => $empresa->id,
    ]);
    $etapa = Etapa::create([
        'nome' => '2º Ano EF', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id,
    ]);
    $turma = Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id, 'nome' => '2º Ano A', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);

    $pessoaAluno = Pessoa::create(['nome' => 'Pedro Pesquisa', 'empresa_id' => $empresa->id]);
    $aluno = Aluno::create([
        'pessoa_id' => $pessoaAluno->id, 'escola_id' => $escola->id,
        'registro_academico' => 'RA-PS1', 'empresa_id' => $empresa->id,
    ]);
    Matricula::create([
        'aluno_id' => $aluno->id, 'turma_id' => $turma->id,
        'ano_letivo_id' => $anoLetivo->id, 'status' => 'ativa',
        'numero_chamada' => 1, 'empresa_id' => $empresa->id,
    ]);

    $pessoaResp = Pessoa::create(['nome' => 'Mãe do Pedro', 'empresa_id' => $empresa->id]);
    $responsavel = Responsavel::create([
        'pessoa_id' => $pessoaResp->id, 'empresa_id' => $empresa->id,
    ]);
    $aluno->responsaveis()->attach($responsavel->id, [
        'tipo' => 'legal', 'principal' => true,
        'pode_retirar' => true, 'autoriza_contato' => true,
    ]);

    return compact('escola', 'turma', 'aluno', 'responsavel');
}

function pesquisaDemo($empresa, $escola, array $over = []): Pesquisa
{
    $pesquisa = Pesquisa::create(array_merge([
        'empresa_id' => $empresa->id,
        'escola_id' => $escola->id,
        'titulo' => 'Pesquisa Demo',
        'data_inicio' => now()->subDay()->toDateString(),
        'data_fim' => now()->addDays(10)->toDateString(),
        'status' => 'aberta',
    ], $over));

    PesquisaPergunta::create([
        'pesquisa_id' => $pesquisa->id, 'ordem' => 1,
        'pergunta' => 'Nota geral da escola?', 'tipo' => 'nota', 'obrigatoria' => 'S',
    ]);
    PesquisaPergunta::create([
        'pesquisa_id' => $pesquisa->id, 'ordem' => 2,
        'pergunta' => 'Recomendaria?', 'tipo' => 'sim_nao', 'obrigatoria' => 'S',
    ]);

    return $pesquisa->fresh('perguntas');
}

test('diretor cria pesquisa com perguntas e visualiza resultados', function () {
    $empresa = tenant();
    $ids = setupPesquisa($empresa);
    $user = usuarioTenant($empresa, 'diretor');

    // index() cria a sessao de filtro/redirect usada pelo POST
    $this->actingAs($user)->get(route('pesquisa.index'))->assertOk();

    $response = $this->actingAs($user)->post(route('pesquisa.store'), [
        'titulo' => 'Satisfação 2026',
        'escola_id' => $ids['escola']->id,
        'data_inicio' => now()->toDateString(),
        'data_fim' => now()->addDays(15)->toDateString(),
        'status' => 'aberta',
        'descricao' => 'Avalie a escola',
        'perguntas' => [
            ['pergunta' => 'Nota da secretaria?', 'tipo' => 'nota', 'obrigatoria' => 'S'],
            ['pergunta' => 'Tem sugestões?', 'tipo' => 'texto', 'obrigatoria' => 'N'],
        ],
    ]);

    $response->assertRedirect(route('pesquisa.index'));

    $pesquisa = Pesquisa::where('titulo', 'Satisfação 2026')->firstOrFail();
    expect($pesquisa->perguntas)->toHaveCount(2);

    $this->actingAs($user)->get(route('pesquisa.index'))->assertOk()->assertSee('Satisfação 2026');
    $this->actingAs($user)->get(route('pesquisa.resultados', $pesquisa->id))->assertOk();
});

test('validacao exige titulo datas e ao menos uma pergunta', function () {
    $empresa = tenant();
    setupPesquisa($empresa);
    $user = usuarioTenant($empresa, 'diretor');

    $this->actingAs($user)->post(route('pesquisa.store'), [])
        ->assertSessionHasErrors(['titulo', 'data_inicio', 'data_fim', 'status', 'perguntas']);
});

test('data fim nao pode ser anterior ao inicio', function () {
    $empresa = tenant();
    $ids = setupPesquisa($empresa);
    $user = usuarioTenant($empresa, 'diretor');

    $this->actingAs($user)->post(route('pesquisa.store'), [
        'titulo' => 'Inválida',
        'escola_id' => $ids['escola']->id,
        'data_inicio' => now()->addDays(10)->toDateString(),
        'data_fim' => now()->addDays(5)->toDateString(),
        'status' => 'aberta',
        'perguntas' => [['pergunta' => 'P?', 'tipo' => 'nota']],
    ])->assertSessionHasErrors('data_fim');
});

test('familia ve pesquisa aberta da escola e responde no portal', function () {
    $empresa = tenant();
    $ids = setupPesquisa($empresa);
    $pesquisa = pesquisaDemo($empresa, $ids['escola'], ['titulo' => 'Pesquisa Aberta Portal']);

    $user = usuarioTenant($empresa, 'responsavel_legal');
    $ids['responsavel']->update(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('portal.pesquisas'))
        ->assertOk()
        ->assertSee('Pesquisa Aberta Portal');

    $respostas = [];
    foreach ($pesquisa->perguntas as $p) {
        $respostas[$p->id] = $p->tipo === 'nota' ? '5' : 'S';
    }

    $this->actingAs($user)->post(route('portal.pesquisa.salvar', $pesquisa->id), [
        'aluno_id' => $ids['aluno']->id,
        'respostas' => $respostas,
    ])->assertRedirect(route('portal.pesquisas'));

    expect(\App\Models\PesquisaResposta::where('pesquisa_id', $pesquisa->id)->where('user_id', $user->id)->exists())->toBeTrue();

    // Pesquisa respondida some da lista
    $this->actingAs($user)
        ->get(route('portal.pesquisas'))
        ->assertOk()
        ->assertDontSee('Pesquisa Aberta Portal');

    // E não pode responder de novo
    $this->actingAs($user)
        ->get(route('portal.pesquisa.responder', $pesquisa->id))
        ->assertForbidden();
});

test('portal nao mostra pesquisa de outra escola nem encerrada', function () {
    $empresa = tenant();
    $ids = setupPesquisa($empresa);

    $outraEscola = Escola::create(['nome' => 'Escola Vizinha', 'empresa_id' => $empresa->id]);
    pesquisaDemo($empresa, $outraEscola, ['titulo' => 'Pesquisa Outra Escola']);
    pesquisaDemo($empresa, $ids['escola'], [
        'titulo' => 'Pesquisa Encerrada',
        'status' => 'encerrada',
        'data_inicio' => now()->subDays(20)->toDateString(),
        'data_fim' => now()->subDays(10)->toDateString(),
    ]);

    $user = usuarioTenant($empresa, 'responsavel_legal');
    $ids['responsavel']->update(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('portal.pesquisas'))
        ->assertOk()
        ->assertDontSee('Pesquisa Outra Escola')
        ->assertDontSee('Pesquisa Encerrada');
});

test('resposta invalida de nota e bloqueada', function () {
    $empresa = tenant();
    $ids = setupPesquisa($empresa);
    $pesquisa = pesquisaDemo($empresa, $ids['escola']);

    $user = usuarioTenant($empresa, 'responsavel_legal');
    $ids['responsavel']->update(['user_id' => $user->id]);

    $this->actingAs($user)->post(route('portal.pesquisa.salvar', $pesquisa->id), [
        'respostas' => [$pesquisa->perguntas[0]->id => '9', $pesquisa->perguntas[1]->id => 'X'],
    ])->assertSessionHasErrors();
});

test('professor le pesquisas mas nao cria', function () {
    $empresa = tenant();
    setupPesquisa($empresa);
    $user = usuarioTenant($empresa, 'professor');

    $this->actingAs($user)->get(route('pesquisa.index'))->assertOk();
    $this->actingAs($user)->get(route('pesquisa.create'))->assertForbidden();
});

test('pesquisa de outro tenant nao aparece na listagem', function () {
    $empresa = tenant();
    $ids = setupPesquisa($empresa);
    pesquisaDemo($empresa, $ids['escola'], ['titulo' => 'Pesquisa Secreta A']);

    $outro = tenant(['nome' => 'Rede B', 'slug' => 'rede-b-pesquisa']);
    app(EmpresaContext::class)->setEmpresaId($outro->id);
    $userB = usuarioTenant($outro, 'diretor');

    $this->actingAs($userB)
        ->get(route('pesquisa.index'))
        ->assertOk()
        ->assertDontSee('Pesquisa Secreta A');
});
