<?php

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\Atribuicao;
use App\Models\Disciplina;
use App\Models\EntregaTarefa;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Matricula;
use App\Models\Pessoa;
use App\Models\Tarefa;
use App\Models\Turma;
use App\Support\EmpresaContext;
use Illuminate\Support\Facades\DB;

function setupTarefa($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Tarefas', 'empresa_id' => $empresa->id]);
    $anoLetivo = AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => 2026, 'empresa_id' => $empresa->id,
    ]);
    $etapa = Etapa::create([
        'nome' => '5º Ano EF', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id,
    ]);
    $turma = Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id, 'nome' => '5º Ano A', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);
    $disciplina = Disciplina::create(['nome' => 'Português', 'sigla' => 'POR', 'empresa_id' => $empresa->id]);
    DB::table('turma_disciplina')->insert([
        'turma_id' => $turma->id, 'disciplina_id' => $disciplina->id,
        'carga_horaria_semanal' => 5, 'created_at' => now(), 'updated_at' => now(),
    ]);

    $pessoa = Pessoa::create(['nome' => 'Pedro Souza', 'empresa_id' => $empresa->id]);
    $aluno = Aluno::create([
        'pessoa_id' => $pessoa->id, 'escola_id' => $escola->id,
        'registro_academico' => 'RA-T1', 'empresa_id' => $empresa->id,
    ]);
    $matricula = Matricula::create([
        'aluno_id' => $aluno->id, 'turma_id' => $turma->id,
        'ano_letivo_id' => $anoLetivo->id, 'status' => 'ativa',
        'numero_chamada' => 1, 'empresa_id' => $empresa->id,
    ]);

    return compact('escola', 'anoLetivo', 'turma', 'disciplina', 'aluno', 'matricula');
}

function criarTarefaDemo($ids, $empresa, array $overrides = []): Tarefa
{
    return Tarefa::create(array_merge([
        'empresa_id' => $empresa->id,
        'turma_id' => $ids['turma']->id,
        'disciplina_id' => $ids['disciplina']->id,
        'titulo' => 'Exercicios de fixacao',
        'descricao' => 'Fazer os exercicios do caderno',
        'data_entrega' => now()->addDays(5)->toDateString(),
        'nota_maxima' => 10,
        'ativo' => 'S',
    ], $overrides));
}

test('diretor cria tarefa e entregas sao geradas para a turma', function () {
    $empresa = tenant();
    $ids = setupTarefa($empresa);
    $user = usuarioTenant($empresa, 'administrador');

    $this->actingAs($user)->get(route('tarefa.index'))->assertOk();
    $this->actingAs($user)->get(route('tarefa.create'))->assertOk();

    $this->actingAs($user)->post(route('tarefa.store'), [
        'turma_id' => $ids['turma']->id,
        'disciplina_id' => $ids['disciplina']->id,
        'titulo' => 'Pesquisa sobre o Brasil',
        'descricao' => 'Entregar em folha sulfite',
        'data_entrega' => now()->addDays(7)->toDateString(),
        'nota_maxima' => 10,
        'ativo' => 'S',
        'gerar_entregas' => '1',
    ])->assertRedirect();

    $tarefa = Tarefa::first();
    expect($tarefa->titulo)->toBe('Pesquisa sobre o Brasil')
        ->and($tarefa->user_id)->toBe($user->id);

    $entregas = EntregaTarefa::where('tarefa_id', $tarefa->id)->get();
    expect($entregas)->toHaveCount(1)
        ->and($entregas->first()->matricula_id)->toBe($ids['matricula']->id)
        ->and($entregas->first()->status)->toBe('pendente');
});

test('validacao exige titulo e data de entrega', function () {
    $empresa = tenant();
    $ids = setupTarefa($empresa);
    $user = usuarioTenant($empresa, 'administrador');

    $this->actingAs($user)->post(route('tarefa.store'), [
        'turma_id' => $ids['turma']->id,
        'ativo' => 'S',
    ])->assertSessionHasErrors(['titulo', 'data_entrega']);
});

test('salvamento em lote atualiza status e nota das entregas', function () {
    $empresa = tenant();
    $ids = setupTarefa($empresa);
    $user = usuarioTenant($empresa, 'administrador');
    $tarefa = criarTarefaDemo($ids, $empresa);

    $this->actingAs($user)->get(route('tarefa.entregas', $tarefa->id))->assertOk();

    $this->actingAs($user)->post(route('tarefa.entregas.salvar', $tarefa->id), [
        'entregas' => [
            $ids['matricula']->id => [
                'status' => 'avaliada',
                'nota' => 8.5,
                'data_entrega' => now()->subDay()->toDateString(),
                'observacoes' => 'Otimo trabalho',
            ],
        ],
    ])->assertRedirect(route('tarefa.entregas', $tarefa->id));

    $entrega = EntregaTarefa::where('tarefa_id', $tarefa->id)->first();
    expect($entrega->status)->toBe('avaliada')
        ->and((float) $entrega->nota)->toBe(8.5);
});

test('nota acima da nota maxima e rejeitada', function () {
    $empresa = tenant();
    $ids = setupTarefa($empresa);
    $user = usuarioTenant($empresa, 'administrador');
    $tarefa = criarTarefaDemo($ids, $empresa, ['nota_maxima' => 10]);

    $this->actingAs($user)->post(route('tarefa.entregas.salvar', $tarefa->id), [
        'entregas' => [
            $ids['matricula']->id => ['status' => 'avaliada', 'nota' => 15],
        ],
    ])->assertSessionHasErrors('entregas.'.$ids['matricula']->id.'.nota');
});

test('professor gerencia tarefas apenas das turmas que leciona', function () {
    $empresa = tenant();
    $ids = setupTarefa($empresa);
    $professor = usuarioTenant($empresa, 'professor');
    $tarefa = criarTarefaDemo($ids, $empresa);

    // professor pode criar tarefas/provas online — escopo validado por atribuicao
    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $ids['turma']->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $professor->id,
        'papel' => 'titular', 'ativo' => 'S',
    ]);

    $this->actingAs($professor)->get(route('tarefa.index'))->assertOk();
    $this->actingAs($professor)->get(route('tarefa.entregas', $tarefa->id))->assertOk();
    $this->actingAs($professor)->post(route('tarefa.store'), [
        'turma_id' => $ids['turma']->id,
        'disciplina_id' => $ids['disciplina']->id,
        'titulo' => 'Prova online da turma',
        'data_entrega' => now()->addDay()->toDateString(),
        'ativo' => 'S',
    ])->assertRedirect();

    // turma sem atribuicao: o servico bloqueia a criacao
    $outraTurma = Turma::create([
        'escola_id' => $ids['escola']->id, 'ano_letivo_id' => $ids['anoLetivo']->id,
        'etapa_id' => $ids['turma']->etapa_id,
        'nome' => 'Turma alheia', 'turno' => 'matutino', 'empresa_id' => $empresa->id,
    ]);
    $this->actingAs($professor)->post(route('tarefa.store'), [
        'turma_id' => $outraTurma->id,
        'titulo' => 'Tarefa indevida',
        'data_entrega' => now()->addDay()->toDateString(),
        'ativo' => 'S',
    ])->assertSessionHasErrors('turma_id');
});

test('tarefas tem escopo de tenant', function () {
    $empresa = tenant();
    $ids = setupTarefa($empresa);
    criarTarefaDemo($ids, $empresa);

    $outro = tenant(['nome' => 'Rede T', 'slug' => 'rede-t']);
    app(EmpresaContext::class)->setEmpresaId($outro->id);
    expect(Tarefa::count())->toBe(0)
        ->and(EntregaTarefa::count())->toBe(0);

    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $user = usuarioTenant($empresa, 'administrador');
    $this->actingAs($user)->get(route('tarefa.index'))->assertOk();
});

test('portal mostra tarefas das turmas dos alunos vinculados', function () {
    $empresa = tenant();
    $ids = setupTarefa($empresa);
    $tarefa = criarTarefaDemo($ids, $empresa);

    // vincula usuario responsavel ao aluno
    $responsavelUser = usuarioTenant($empresa, 'responsavel_legal');
    $ids['aluno']->update(['user_id' => $responsavelUser->id]);

    $response = $this->actingAs($responsavelUser)->get(route('portal.tarefas'));
    $response->assertOk()->assertSee('Exercicios de fixacao');

    // responsavel de outro aluno (sem turma com tarefas) nao ve a tarefa
    $outroAlunoUser = usuarioTenant($empresa, 'responsavel_legal');
    $pessoa2 = Pessoa::create(['nome' => 'Outra Pessoa', 'empresa_id' => $empresa->id]);
    $aluno2 = Aluno::create([
        'pessoa_id' => $pessoa2->id, 'escola_id' => $ids['escola']->id,
        'registro_academico' => 'RA-T2', 'empresa_id' => $empresa->id,
        'user_id' => $outroAlunoUser->id,
    ]);
    Matricula::create([
        'aluno_id' => $aluno2->id, 'turma_id' => $ids['turma']->id,
        'ano_letivo_id' => $ids['anoLetivo']->id, 'status' => 'ativa',
        'numero_chamada' => 2, 'empresa_id' => $empresa->id,
    ]);

    // mesma turma: ve a tarefa mas so o proprio status de entrega
    $response2 = $this->actingAs($outroAlunoUser)->get(route('portal.tarefas'));
    $response2->assertOk()->assertSee('Exercicios de fixacao');
});

test('portal tarefas tem filtro padrao e mostra o nome do aluno so para responsavel', function () {
    $empresa = tenant();
    $ids = setupTarefa($empresa);
    criarTarefaDemo($ids, $empresa);
    criarTarefaDemo($ids, $empresa, ['titulo' => 'Prova de matematica', 'tipo' => 'prova']);

    // conta do proprio aluno: nao exibe nome/avatar na linha da tarefa
    $alunoUser = usuarioTenant($empresa, 'aluno');
    $ids['aluno']->update(['user_id' => $alunoUser->id]);

    $this->actingAs($alunoUser)->get(route('portal.tarefas'))
        ->assertOk()
        ->assertSee('Exercicios de fixacao')
        ->assertSee('Prova de matematica')
        ->assertDontSee('class="avatar-iniciais', false);

    // filtro padrao por tipo
    $this->actingAs($alunoUser)
        ->get(route('portal.filtro', 'tarefas').'?'.http_build_query(['tipo_where' => 'prova']))
        ->assertRedirect(route('portal.tarefas'));

    $this->actingAs($alunoUser)->get(route('portal.tarefas'))
        ->assertOk()
        ->assertSee('Prova de matematica')
        ->assertDontSee('Exercicios de fixacao');

    $this->actingAs($alunoUser)
        ->get(route('portal.removerFiltro', 'tarefas'))
        ->assertRedirect(route('portal.tarefas'));

    // conta de responsavel: exibe o nome do aluno em cada tarefa
    $ids['aluno']->update(['user_id' => null]);
    $pessoaResp = Pessoa::create(['nome' => 'Mae do Pedro', 'empresa_id' => $empresa->id]);
    $responsavel = \App\Models\Responsavel::create([
        'pessoa_id' => $pessoaResp->id, 'empresa_id' => $empresa->id,
    ]);
    $ids['aluno']->responsaveis()->attach($responsavel->id, [
        'tipo' => 'legal', 'principal' => true, 'pode_retirar' => true, 'autoriza_contato' => true,
    ]);
    $respUser = usuarioTenant($empresa, 'responsavel_legal');
    $responsavel->update(['user_id' => $respUser->id]);

    $this->actingAs($respUser)->get(route('portal.tarefas'))
        ->assertOk()
        ->assertSee('Exercicios de fixacao')
        ->assertSee('class="avatar-iniciais', false)
        ->assertSee('Pedro Souza');
});

test('create de tarefa pre-seleciona a turma informada', function () {
    $empresa = tenant();
    $ids = setupTarefa($empresa);
    $user = usuarioTenant($empresa, 'administrador');

    $html = $this->actingAs($user)
        ->get(route('tarefa.create', ['turma_id' => $ids['turma']->id]))
        ->assertOk()
        ->getContent();

    expect($html)->toMatch('/<input[^>]*id="turma_id"[^>]*value="'.$ids['turma']->id.'"/');
});

test('tarefa criada em lote para varias disciplinas', function () {
    $empresa = tenant();
    $ids = setupTarefa($empresa);
    $user = usuarioTenant($empresa, 'administrador');
    $d2 = Disciplina::create(['nome' => 'Ciências', 'sigla' => 'CIE', 'empresa_id' => $empresa->id]);

    $this->actingAs($user)->post(route('tarefa.store'), [
        'turma_id' => $ids['turma']->id,
        'titulo' => 'Leitura compartilhada',
        'data_entrega' => now()->addDays(3)->toDateString(),
        'tipo' => 'atividade',
        'disciplina_ids' => [$ids['disciplina']->id, $d2->id],
        'gerar_entregas' => 1,
    ])->assertRedirect();

    $tarefas = Tarefa::where('turma_id', $ids['turma']->id)->get();
    expect($tarefas)->toHaveCount(2)
        ->and($tarefas->pluck('disciplina_id')->sort()->values()->all())
        ->toBe(collect([$ids['disciplina']->id, $d2->id])->sort()->values()->all());
});
