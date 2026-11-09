<?php

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\Atribuicao;
use App\Models\Disciplina;
use App\Models\DocumentoSolicitacao;
use App\Models\EntregaTarefa;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Matricula;
use App\Models\Pessoa;
use App\Models\Responsavel;
use App\Models\Tarefa;
use App\Models\Turma;
use App\Support\EmpresaContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

function setupPortalClassroom($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Central', 'empresa_id' => $empresa->id]);
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
    $disciplina = Disciplina::create(['nome' => 'Matemática', 'sigla' => 'MAT', 'empresa_id' => $empresa->id]);

    DB::table('turma_disciplina')->insert([
        'turma_id' => $turma->id, 'disciplina_id' => $disciplina->id,
        'carga_horaria_semanal' => 5, 'created_at' => now(), 'updated_at' => now(),
    ]);

    $pessoaAluno = Pessoa::create(['nome' => 'Maria Silva', 'empresa_id' => $empresa->id]);
    $aluno = Aluno::create([
        'pessoa_id' => $pessoaAluno->id, 'escola_id' => $escola->id,
        'registro_academico' => 'RA-0001', 'empresa_id' => $empresa->id,
    ]);
    $matricula = Matricula::create([
        'aluno_id' => $aluno->id, 'turma_id' => $turma->id,
        'ano_letivo_id' => $anoLetivo->id, 'status' => 'ativa',
        'numero_chamada' => 1, 'empresa_id' => $empresa->id,
    ]);

    $pessoaResp = Pessoa::create(['nome' => 'João Pai', 'empresa_id' => $empresa->id]);
    $responsavel = Responsavel::create([
        'pessoa_id' => $pessoaResp->id, 'empresa_id' => $empresa->id,
    ]);
    $aluno->responsaveis()->attach($responsavel->id, [
        'tipo' => 'legal', 'principal' => true, 'pode_retirar' => true, 'autoriza_contato' => true,
    ]);

    return compact('escola', 'anoLetivo', 'etapa', 'turma', 'disciplina', 'aluno', 'matricula', 'responsavel');
}

function usuarioPortalClassroom($empresa, $responsavel)
{
    $user = usuarioTenant($empresa, 'responsavel_legal');
    $responsavel->update(['user_id' => $user->id]);

    return $user;
}

function usuarioAlunoPortalClassroom($empresa, $aluno)
{
    $user = usuarioTenant($empresa, 'aluno');
    $aluno->update(['user_id' => $user->id]);

    return $user;
}

test('responsavel abre todas as telas novas do menu do portal', function () {
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $user = usuarioPortalClassroom($empresa, $ids['responsavel']);

    foreach (['portal.disciplinas', 'portal.turmas', 'portal.frequencia', 'portal.notas',
        'portal.alertas', 'portal.solicitacoes', 'portal.documentos'] as $rota) {
        $this->actingAs($user)->get(route($rota))->assertOk();
    }
});

test('responsavel acessa hub da turma do filho', function () {
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $user = usuarioPortalClassroom($empresa, $ids['responsavel']);

    $this->actingAs($user)
        ->get(route('portal.turma', $ids['turma']->id))
        ->assertOk()
        ->assertSee('1º Ano A')
        ->assertSee('Avisos')
        ->assertSee('Disciplinas')
        ->assertSee('Materiais')
        ->assertSee('Pessoas');

    $this->actingAs($user)
        ->get(route('portal.turma', [$ids['turma']->id, 'aba' => 'pessoas']))
        ->assertOk()
        ->assertSee('Maria Silva');
});

test('responsavel nao acessa turma sem matricula do filho', function () {
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $user = usuarioPortalClassroom($empresa, $ids['responsavel']);

    $outraTurma = Turma::create([
        'escola_id' => $ids['escola']->id, 'ano_letivo_id' => $ids['anoLetivo']->id,
        'etapa_id' => $ids['etapa']->id, 'nome' => '2º Ano B', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);

    $this->actingAs($user)
        ->get(route('portal.turma', $outraTurma->id))
        ->assertForbidden();
});

test('aluno abre topico e comenta no forum da disciplina', function () {
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $user = usuarioAlunoPortalClassroom($empresa, $ids['aluno']);

    $this->actingAs($user)
        ->post(route('portal.turma.disciplina.forum.store', [$ids['turma']->id, $ids['disciplina']->id]), [
            'mensagem' => 'A prova cobre o capítulo 4?',
        ])->assertRedirect();

    $post = \App\Models\TurmaForumPost::where('turma_id', $ids['turma']->id)->first();
    expect($post)->not->toBeNull()
        ->and($post->mensagem)->toBe('A prova cobre o capítulo 4?')
        ->and($post->titulo)->toBeNull()
        ->and($post->disciplina_id)->toBe($ids['disciplina']->id);

    $this->actingAs($user)
        ->post(route('portal.turma.disciplina.forum.comentar', [$ids['turma']->id, $ids['disciplina']->id, $post->id]), [
            'mensagem' => 'Complementando a pergunta...',
        ])->assertRedirect();

    expect($post->respostas()->count())->toBe(1)
        ->and($post->respostas()->first()->disciplina_id)->toBe($ids['disciplina']->id);

    $this->actingAs($user)
        ->get(route('portal.turma.disciplina', [$ids['turma']->id, $ids['disciplina']->id]))
        ->assertOk()
        ->assertSee('Fórum')
        ->assertSee('A prova cobre o capítulo 4?');
});

test('aluno ve nao lidas no forum e marcar como lida limpa o badge', function () {
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $user = usuarioAlunoPortalClassroom($empresa, $ids['aluno']);
    $professor = usuarioTenant($empresa, 'professor');

    // topico + resposta de outro autor; resposta propria nao conta
    $post = \App\Models\TurmaForumPost::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'turma_id' => $ids['turma']->id, 'disciplina_id' => $ids['disciplina']->id,
        'user_id' => $professor->id, 'titulo' => 'Dúvidas', 'mensagem' => 'Pergunta?',
    ]);
    \App\Models\TurmaForumPost::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'turma_id' => $ids['turma']->id, 'disciplina_id' => $ids['disciplina']->id,
        'parent_id' => $post->id, 'user_id' => $professor->id, 'mensagem' => 'Detalhe',
    ]);
    \App\Models\TurmaForumPost::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'turma_id' => $ids['turma']->id, 'disciplina_id' => $ids['disciplina']->id,
        'parent_id' => $post->id, 'user_id' => $user->id, 'mensagem' => 'Minha resposta',
    ]);

    // badge = 2 nao lidas (post + resposta alheia) e divisor no chat
    $html = $this->actingAs($user)
        ->get(route('portal.turma.disciplina.forum.posts', [$ids['turma']->id, $ids['disciplina']->id]))
        ->assertOk()
        ->json('html');

    expect($html)->toContain('wa-topic-count')
        ->toContain('2 mensagens não lidas');

    // abrir a conversa marca como lida
    $this->actingAs($user)
        ->post(route('portal.turma.disciplina.forum.lida', [$ids['turma']->id, $ids['disciplina']->id, $post->id]))
        ->assertOk();

    $this->assertDatabaseHas('turma_forum_leituras', [
        'post_id' => $post->id,
        'user_id' => $user->id,
    ]);

    $html = $this->actingAs($user)
        ->get(route('portal.turma.disciplina.forum.posts', [$ids['turma']->id, $ids['disciplina']->id]))
        ->assertOk()
        ->json('html');

    expect($html)->not->toContain('wa-topic-count')
        ->not->toContain('mensagens não lidas');

    // mensagem que chega com a conversa aberta (?aberta) ja conta como lida
    \App\Models\TurmaForumPost::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'turma_id' => $ids['turma']->id, 'disciplina_id' => $ids['disciplina']->id,
        'parent_id' => $post->id, 'user_id' => $professor->id, 'mensagem' => 'Chegou agora',
    ]);

    $html = $this->actingAs($user)
        ->get(route('portal.turma.disciplina.forum.posts', [$ids['turma']->id, $ids['disciplina']->id, 'aberta' => $post->id]))
        ->assertOk()
        ->json('html');

    expect($html)->not->toContain('mensagens não lidas');
});

test('aluno nao posta no forum de disciplina de outra turma', function () {
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $user = usuarioAlunoPortalClassroom($empresa, $ids['aluno']);

    $outraTurma = Turma::create([
        'escola_id' => $ids['escola']->id, 'ano_letivo_id' => $ids['anoLetivo']->id,
        'etapa_id' => $ids['etapa']->id, 'nome' => '3º Ano C', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);

    // disciplina nao ofertada pela turma do filho
    $this->actingAs($user)
        ->post(route('portal.turma.disciplina.forum.store', [$outraTurma->id, $ids['disciplina']->id]), [
            'titulo' => 'Invasão', 'mensagem' => 'Não devia passar',
        ])->assertForbidden();
});

test('responsavel acompanha a disciplina mas nao participa do forum', function () {
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $user = usuarioPortalClassroom($empresa, $ids['responsavel']);

    // a tela da disciplina abre normalmente, mas sem a aba do forum
    $this->actingAs($user)
        ->get(route('portal.turma.disciplina', [$ids['turma']->id, $ids['disciplina']->id]))
        ->assertOk()
        ->assertSee('Matemática')
        ->assertDontSee('Fórum');

    $this->actingAs($user)
        ->post(route('portal.turma.disciplina.forum.store', [$ids['turma']->id, $ids['disciplina']->id]), [
            'titulo' => 'Tentativa', 'mensagem' => 'Não devia passar',
        ])->assertForbidden();

    $post = \App\Models\TurmaForumPost::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'turma_id' => $ids['turma']->id, 'disciplina_id' => $ids['disciplina']->id,
        'user_id' => $user->id, 'titulo' => 'Tópico do professor', 'mensagem' => 'Aviso',
    ]);

    $this->actingAs($user)
        ->post(route('portal.turma.disciplina.forum.comentar', [$ids['turma']->id, $ids['disciplina']->id, $post->id]), [
            'mensagem' => 'Comentário indevido',
        ])->assertForbidden();

    $this->actingAs($user)
        ->get(route('portal.turma.disciplina.forum.posts', [$ids['turma']->id, $ids['disciplina']->id]))
        ->assertForbidden();
});

test('responsavel acompanha a tarefa mas nao envia resposta', function () {
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $user = usuarioPortalClassroom($empresa, $ids['responsavel']);

    $tarefa = Tarefa::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'turma_id' => $ids['turma']->id, 'disciplina_id' => $ids['disciplina']->id,
        'titulo' => 'Atividade de casa', 'data_entrega' => now()->addDays(3)->toDateString(),
        'ativo' => 'S', 'tipo_entrega' => 'leitura',
    ]);

    // leitura da tarefa e liberada; o envio, nao
    $this->actingAs($user)
        ->get(route('portal.turma.tarefa', [$ids['turma']->id, $tarefa->id]))
        ->assertOk()
        ->assertSee('apenas o aluno pode responder', false);

    $this->actingAs($user)
        ->post(route('portal.turma.tarefa.responder', [$ids['turma']->id, $tarefa->id]), [
            'matricula_id' => $ids['matricula']->id,
            'modo' => 'entrega',
        ])->assertForbidden();

    expect(EntregaTarefa::where('tarefa_id', $tarefa->id)->count())->toBe(0);
});

test('home do portal mostra alunos vinculados so para responsavel', function () {
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $responsavel = usuarioPortalClassroom($empresa, $ids['responsavel']);
    $aluno = usuarioAlunoPortalClassroom($empresa, $ids['aluno']);

    $this->actingAs($responsavel)
        ->get(route('portal.index'))
        ->assertOk()
        ->assertSee('Alunos vinculados')
        ->assertSee('Maria Silva');

    $this->actingAs($aluno)
        ->get(route('portal.index'))
        ->assertOk()
        ->assertDontSee('Minha matrícula')
        ->assertDontSee('Alunos vinculados');
});

test('professor publica orientacao no forum da disciplina que leciona', function () {
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $professor = usuarioTenant($empresa, 'professor');

    Atribuicao::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'turma_id' => $ids['turma']->id, 'disciplina_id' => $ids['disciplina']->id,
        'user_id' => $professor->id, 'papel' => 'titular', 'ativo' => 'S',
    ]);

    $this->actingAs($professor)
        ->post(route('turma.forum.store', [$ids['turma']->id, $ids['disciplina']->id]), [
            'mensagem' => 'Estudem os capítulos 3 e 4.',
        ])->assertRedirect();

    $this->assertDatabaseHas('turma_forum_posts', [
        'turma_id' => $ids['turma']->id,
        'disciplina_id' => $ids['disciplina']->id,
        'mensagem' => 'Estudem os capítulos 3 e 4.',
    ]);
});

test('professor acessa a tela da disciplina e ve o forum', function () {
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $professor = usuarioTenant($empresa, 'professor');

    Atribuicao::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'turma_id' => $ids['turma']->id, 'disciplina_id' => $ids['disciplina']->id,
        'user_id' => $professor->id, 'papel' => 'titular', 'ativo' => 'S',
    ]);

    $this->actingAs($professor)
        ->get(route('turma.disciplina.show', [$ids['turma']->id, $ids['disciplina']->id]))
        ->assertOk()
        ->assertSee('Fórum')
        ->assertSee('Materiais');
});

test('professor nao acessa nem publica em disciplina que nao leciona', function () {
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $professor = usuarioTenant($empresa, 'professor');

    Atribuicao::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'turma_id' => $ids['turma']->id, 'disciplina_id' => $ids['disciplina']->id,
        'user_id' => $professor->id, 'papel' => 'titular', 'ativo' => 'S',
    ]);
    $outraDisciplina = Disciplina::create(['nome' => 'História', 'empresa_id' => $empresa->id]);
    DB::table('turma_disciplina')->insert([
        'turma_id' => $ids['turma']->id, 'disciplina_id' => $outraDisciplina->id,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $this->actingAs($professor)
        ->get(route('turma.disciplina.show', [$ids['turma']->id, $outraDisciplina->id]))
        ->assertForbidden();

    $this->actingAs($professor)
        ->post(route('turma.forum.store', [$ids['turma']->id, $outraDisciplina->id]), [
            'titulo' => 'Orientação indevida',
            'mensagem' => 'Não devia passar',
        ])->assertStatus(422);
});

test('aluno edita titulo e encerra a propria conversa do forum', function () {
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $user = usuarioAlunoPortalClassroom($empresa, $ids['aluno']);

    $post = \App\Models\TurmaForumPost::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'turma_id' => $ids['turma']->id, 'disciplina_id' => $ids['disciplina']->id,
        'user_id' => $user->id, 'mensagem' => 'Dúvida sobre a lição',
    ]);

    // o autor edita o titulo
    $this->actingAs($user)
        ->put(route('portal.turma.disciplina.forum.update', [$ids['turma']->id, $ids['disciplina']->id, $post->id]), [
            'titulo' => 'Dúvida — capítulo 4',
        ])->assertRedirect();

    expect($post->fresh()->titulo)->toBe('Dúvida — capítulo 4');

    // o autor encerra a conversa
    $this->actingAs($user)
        ->patch(route('portal.turma.disciplina.forum.encerrar', [$ids['turma']->id, $ids['disciplina']->id, $post->id]))
        ->assertRedirect();

    expect($post->fresh()->estaEncerrada())->toBeTrue()
        ->and($post->fresh()->encerrada_por)->toBe($user->id);

    // conversa encerrada nao aceita novas respostas
    $this->actingAs($user)
        ->post(route('portal.turma.disciplina.forum.comentar', [$ids['turma']->id, $ids['disciplina']->id, $post->id]), [
            'mensagem' => 'Tentativa tardia',
        ])->assertForbidden();

    // o autor reabre
    $this->actingAs($user)
        ->patch(route('portal.turma.disciplina.forum.encerrar', [$ids['turma']->id, $ids['disciplina']->id, $post->id]))
        ->assertRedirect();

    expect($post->fresh()->estaEncerrada())->toBeFalse();
});

test('aluno nao edita nem encerra topico de outro usuario', function () {
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $user = usuarioAlunoPortalClassroom($empresa, $ids['aluno']);
    $outro = usuarioPortalClassroom($empresa, $ids['responsavel']);

    $post = \App\Models\TurmaForumPost::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'turma_id' => $ids['turma']->id, 'disciplina_id' => $ids['disciplina']->id,
        'user_id' => $outro->id, 'titulo' => 'Tópico alheio', 'mensagem' => 'Não mexa',
    ]);

    $this->actingAs($user)
        ->put(route('portal.turma.disciplina.forum.update', [$ids['turma']->id, $ids['disciplina']->id, $post->id]), [
            'titulo' => 'Título invadido',
        ])->assertForbidden();

    $this->actingAs($user)
        ->patch(route('portal.turma.disciplina.forum.encerrar', [$ids['turma']->id, $ids['disciplina']->id, $post->id]))
        ->assertForbidden();

    expect($post->fresh()->titulo)->toBe('Tópico alheio')
        ->and($post->fresh()->estaEncerrada())->toBeFalse();
});

test('professor modera o forum: edita titulo, encerra e remove qualquer topico', function () {
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $professor = usuarioTenant($empresa, 'professor');
    $alunoUser = usuarioAlunoPortalClassroom($empresa, $ids['aluno']);

    Atribuicao::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'turma_id' => $ids['turma']->id, 'disciplina_id' => $ids['disciplina']->id,
        'user_id' => $professor->id, 'papel' => 'titular', 'ativo' => 'S',
    ]);

    $post = \App\Models\TurmaForumPost::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'turma_id' => $ids['turma']->id, 'disciplina_id' => $ids['disciplina']->id,
        'user_id' => $alunoUser->id, 'mensagem' => 'Pergunta do aluno',
    ]);
    $resposta = \App\Models\TurmaForumPost::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'turma_id' => $ids['turma']->id, 'disciplina_id' => $ids['disciplina']->id,
        'parent_id' => $post->id, 'user_id' => $alunoUser->id, 'mensagem' => 'Complemento',
    ]);

    // aluno nao remove: a rota de remocao e so do professor
    $this->actingAs($alunoUser)
        ->delete(route('turma.forum.destroy', [$ids['turma']->id, $ids['disciplina']->id, $post->id]))
        ->assertForbidden();

    // professor edita o titulo de topico alheio (moderacao)
    $this->actingAs($professor)
        ->put(route('turma.forum.update', [$ids['turma']->id, $ids['disciplina']->id, $post->id]), [
            'titulo' => 'Dúvida — capítulo 4',
        ])->assertRedirect();

    expect($post->fresh()->titulo)->toBe('Dúvida — capítulo 4');

    // professor encerra a conversa — nem ele responde mais
    $this->actingAs($professor)
        ->patch(route('turma.forum.encerrar', [$ids['turma']->id, $ids['disciplina']->id, $post->id]))
        ->assertRedirect();

    expect($post->fresh()->estaEncerrada())->toBeTrue();

    $this->actingAs($professor)
        ->post(route('turma.forum.comentar', [$ids['turma']->id, $ids['disciplina']->id, $post->id]), [
            'mensagem' => 'Resposta tardia',
        ])->assertForbidden();

    // professor remove o topico com as respostas (soft delete)
    $this->actingAs($professor)
        ->delete(route('turma.forum.destroy', [$ids['turma']->id, $ids['disciplina']->id, $post->id]))
        ->assertRedirect();

    expect(\App\Models\TurmaForumPost::withTrashed()->find($post->id)->trashed())->toBeTrue()
        ->and(\App\Models\TurmaForumPost::withTrashed()->find($resposta->id)->trashed())->toBeTrue();
});

test('aluno responde prova objetiva e recebe nota automatica', function () {
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $user = usuarioAlunoPortalClassroom($empresa, $ids['aluno']);

    $tarefa = Tarefa::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'turma_id' => $ids['turma']->id, 'disciplina_id' => $ids['disciplina']->id,
        'titulo' => 'Prova de matemática', 'data_entrega' => now()->addDays(3)->toDateString(),
        'nota_maxima' => 10, 'ativo' => 'S',
    ]);
    $q1 = $tarefa->questoes()->create([
        'empresa_id' => $empresa->id, 'ordem' => 1, 'tipo' => 'objetiva',
        'enunciado' => 'Quanto é 2+2?',
        'alternativas' => ['A' => '3', 'B' => '4', 'C' => '5'], 'gabarito' => 'B',
    ]);
    $q2 = $tarefa->questoes()->create([
        'empresa_id' => $empresa->id, 'ordem' => 2, 'tipo' => 'objetiva',
        'enunciado' => 'Quanto é 3x3?',
        'alternativas' => ['A' => '6', 'B' => '9', 'C' => '12'], 'gabarito' => 'B',
    ]);

    $this->actingAs($user)
        ->post(route('portal.turma.tarefa.responder', [$ids['turma']->id, $tarefa->id]), [
            'matricula_id' => $ids['matricula']->id,
            'respostas' => [$q1->id => 'B', $q2->id => 'A'], // 1 certa, 1 errada
        ])->assertRedirect();

    $entrega = EntregaTarefa::where('tarefa_id', $tarefa->id)
        ->where('matricula_id', $ids['matricula']->id)->first();

    expect($entrega->status)->toBe('avaliada')
        ->and((float) $entrega->nota)->toBe(5.0);
    expect($entrega->respostas()->where('correta', true)->count())->toBe(1);
});

test('tarefa com discursiva fica aguardando correcao', function () {
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $user = usuarioAlunoPortalClassroom($empresa, $ids['aluno']);

    $tarefa = Tarefa::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'turma_id' => $ids['turma']->id, 'disciplina_id' => $ids['disciplina']->id,
        'titulo' => 'Redação', 'data_entrega' => now()->addDays(3)->toDateString(),
        'nota_maxima' => 10, 'ativo' => 'S',
    ]);
    $q = $tarefa->questoes()->create([
        'empresa_id' => $empresa->id, 'ordem' => 1, 'tipo' => 'discursiva',
        'enunciado' => 'Explique a fotossíntese.',
    ]);

    $this->actingAs($user)
        ->post(route('portal.turma.tarefa.responder', [$ids['turma']->id, $tarefa->id]), [
            'matricula_id' => $ids['matricula']->id,
            'respostas' => [$q->id => 'Fotossíntese é o processo...'],
        ])->assertRedirect();

    $entrega = EntregaTarefa::where('tarefa_id', $tarefa->id)
        ->where('matricula_id', $ids['matricula']->id)->first();

    expect($entrega->status)->toBe('entregue')->and($entrega->nota)->toBeNull();
});

test('aluno nao envia resposta para tarefa de outra turma', function () {
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $user = usuarioAlunoPortalClassroom($empresa, $ids['aluno']);

    $outraTurma = Turma::create([
        'escola_id' => $ids['escola']->id, 'ano_letivo_id' => $ids['anoLetivo']->id,
        'etapa_id' => $ids['etapa']->id, 'nome' => '3º Ano C', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);
    $tarefa = Tarefa::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'turma_id' => $outraTurma->id, 'titulo' => 'Prova alheia',
        'data_entrega' => now()->addDays(3)->toDateString(), 'ativo' => 'S',
    ]);

    $this->actingAs($user)
        ->post(route('portal.turma.tarefa.responder', [$outraTurma->id, $tarefa->id]), [
            'matricula_id' => $ids['matricula']->id,
            'respostas' => [],
        ])->assertForbidden();
});

test('familia cria solicitacao e envia documento pedido', function () {
    Storage::fake('public');
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $user = usuarioPortalClassroom($empresa, $ids['responsavel']);

    $this->actingAs($user)
        ->post(route('portal.solicitacoes.store'), [
            'aluno_id' => $ids['aluno']->id,
            'tipo' => 'declaracao',
            'titulo' => 'Declaração de matrícula',
        ])->assertRedirect();

    $this->assertDatabaseHas('solicitacoes', [
        'aluno_id' => $ids['aluno']->id, 'tipo' => 'declaracao', 'status' => 'aberta',
    ]);

    $doc = DocumentoSolicitacao::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'aluno_id' => $ids['aluno']->id, 'tipo' => 'rg',
        'titulo' => 'RG do aluno', 'status' => 'pendente',
    ]);

    $this->actingAs($user)
        ->post(route('portal.documentos.enviar', $doc->id), [
            'arquivo' => \Illuminate\Http\UploadedFile::fake()->create('rg.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

    expect($doc->fresh()->status)->toBe('enviado');
});

test('familia com um so aluno nao informa o aluno na solicitacao', function () {
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $user = usuarioPortalClassroom($empresa, $ids['responsavel']);

    $this->actingAs($user)
        ->get(route('portal.solicitacoes'))
        ->assertOk()
        ->assertDontSee('name="aluno_id"', false);

    $this->actingAs($user)
        ->post(route('portal.solicitacoes.store'), [
            'tipo' => 'declaracao',
            'titulo' => 'Declaração de matrícula',
        ])->assertRedirect();

    $this->assertDatabaseHas('solicitacoes', [
        'aluno_id' => $ids['aluno']->id, 'tipo' => 'declaracao', 'status' => 'aberta',
    ]);
});

test('familia envia solicitacao com campos e anexo do tipo cadastrado', function () {
    Storage::fake('public');
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $user = usuarioPortalClassroom($empresa, $ids['responsavel']);

    $tipo = \App\Models\SolicitacaoTipo::create([
        'empresa_id' => $empresa->id,
        'nome' => 'Pedido de transferência',
        'exige_anexo' => true,
        'campos' => [
            ['rotulo' => 'Escola de destino', 'tipo' => 'texto', 'obrigatoria' => true, 'opcoes' => []],
            ['rotulo' => 'Motivo', 'tipo' => 'selecao', 'obrigatoria' => false, 'opcoes' => ['Mudança de cidade', 'Outro']],
        ],
    ]);

    // anexo obrigatorio ausente
    $this->actingAs($user)
        ->post(route('portal.solicitacoes.store'), [
            'aluno_id' => $ids['aluno']->id,
            'tipo_id' => $tipo->id,
            'campos' => ['Escola Nova', 'Outro'],
        ])->assertSessionHasErrors('anexos');

    // campo obrigatorio ausente
    $this->actingAs($user)
        ->post(route('portal.solicitacoes.store'), [
            'aluno_id' => $ids['aluno']->id,
            'tipo_id' => $tipo->id,
            'campos' => ['', 'Outro'],
            'anexos' => [\Illuminate\Http\UploadedFile::fake()->create('historico.pdf', 100, 'application/pdf')],
        ])->assertSessionHasErrors('campos.0');

    $this->actingAs($user)
        ->post(route('portal.solicitacoes.store'), [
            'aluno_id' => $ids['aluno']->id,
            'tipo_id' => $tipo->id,
            'descricao' => 'Mudança no próximo semestre',
            'campos' => ['Escola Nova', 'Mudança de cidade'],
            'anexos' => [\Illuminate\Http\UploadedFile::fake()->create('historico.pdf', 100, 'application/pdf')],
        ])->assertRedirect();

    $solicitacao = \App\Models\Solicitacao::where('tipo_solicitacao_id', $tipo->id)->first();

    expect($solicitacao)->not->toBeNull()
        ->and($solicitacao->titulo)->toBe('Pedido de transferência')
        ->and($solicitacao->tipo)->toBe('outro')
        ->and($solicitacao->status)->toBe('aberta')
        ->and($solicitacao->dados)->toBe([
            ['campo' => 'Escola de destino', 'valor' => 'Escola Nova'],
            ['campo' => 'Motivo', 'valor' => 'Mudança de cidade'],
        ]);
    expect($solicitacao->anexos)->toHaveCount(1);
});

test('familia nao usa tipo inativo ou de outra escola', function () {
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $user = usuarioPortalClassroom($empresa, $ids['responsavel']);

    $inativo = \App\Models\SolicitacaoTipo::create([
        'empresa_id' => $empresa->id, 'nome' => 'Inativo', 'ativo' => false,
    ]);
    $outraEscola = \App\Models\SolicitacaoTipo::create([
        'empresa_id' => $empresa->id, 'escola_id' => Escola::create(['nome' => 'Outra', 'empresa_id' => $empresa->id])->id,
        'nome' => 'Só da outra unidade',
    ]);

    foreach ([$inativo, $outraEscola] as $tipo) {
        $this->actingAs($user)
            ->post(route('portal.solicitacoes.store'), [
                'aluno_id' => $ids['aluno']->id,
                'tipo_id' => $tipo->id,
            ])->assertNotFound();
    }
});

test('familia e secretaria conversam dentro da solicitacao', function () {
    Storage::fake('public');
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $familia = usuarioPortalClassroom($empresa, $ids['responsavel']);
    $secretaria = usuarioTenant($empresa, 'administrador');

    $this->actingAs($familia)
        ->post(route('portal.solicitacoes.store'), [
            'aluno_id' => $ids['aluno']->id,
            'tipo' => 'declaracao',
            'titulo' => 'Declaração de matrícula',
        ])->assertRedirect();

    $solicitacao = \App\Models\Solicitacao::firstOrFail();

    // secretaria pede esclarecimento sem encerrar a solicitacao
    $this->actingAs($secretaria)
        ->post(route('solicitacao.mensagem', $solicitacao->id), [
            'mensagem' => 'Para qual finalidade é a declaração?',
        ])->assertRedirect(route('solicitacao.index'));

    expect($solicitacao->fresh()->status)->toBe('em_analise')
        ->and($solicitacao->mensagens()->count())->toBe(1);

    // familia responde anexando o documento pedido
    $this->actingAs($familia)
        ->post(route('portal.solicitacoes.mensagem', $solicitacao->id), [
            'mensagem' => 'Para apresentação no conselho tutelar',
            'anexos' => [\Illuminate\Http\UploadedFile::fake()->create('requerimento.pdf', 100, 'application/pdf')],
        ])->assertRedirect();

    $solicitacao = $solicitacao->fresh();
    expect($solicitacao->status)->toBe('aberta')
        ->and($solicitacao->mensagens()->count())->toBe(2)
        ->and($solicitacao->anexos()->whereNotNull('mensagem_id')->count())->toBe(1);

    // resposta final da secretaria tambem entra no historico da conversa
    $this->actingAs($secretaria)
        ->put(route('solicitacao.responder', $solicitacao->id), [
            'status' => 'atendida',
            'resposta' => 'Declaração disponível para retirada na secretaria.',
        ])->assertRedirect();

    $solicitacao = $solicitacao->fresh();
    expect($solicitacao->status)->toBe('atendida')
        ->and($solicitacao->mensagens()->count())->toBe(3);

    // conversa encerrada: familia nao responde mais
    $this->actingAs($familia)
        ->post(route('portal.solicitacoes.mensagem', $solicitacao->id), [
            'mensagem' => 'Obrigado!',
        ])->assertStatus(422);

    // familia nao responde em solicitacao de outro usuario
    $outro = \App\Models\Solicitacao::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'aluno_id' => $ids['aluno']->id, 'user_id' => $secretaria->id,
        'tipo' => 'outro', 'titulo' => 'Pedido de outro', 'status' => 'aberta',
    ]);

    $this->actingAs($familia)
        ->post(route('portal.solicitacoes.mensagem', $outro->id), [
            'mensagem' => 'Tentativa',
        ])->assertNotFound();
});

test('secretaria cadastra e edita tipo de solicitacao', function () {
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $admin = usuarioTenant($empresa, 'administrador');

    $this->actingAs($admin)->get(route('tipo_solicitacao.index'))->assertOk();
    $this->actingAs($admin)->get(route('tipo_solicitacao.create'))->assertOk();

    $this->actingAs($admin)
        ->post(route('tipo_solicitacao.store'), [
            'nome' => 'Declaração de vínculo',
            'exige_anexo' => 'S',
            'ativo' => 'S',
            'campos' => [
                ['rotulo' => 'Finalidade', 'tipo' => 'texto', 'obrigatoria' => 'S', 'opcoes' => ''],
                ['rotulo' => 'Formato', 'tipo' => 'selecao', 'obrigatoria' => 'N', 'opcoes' => 'Digital, Impressa'],
            ],
        ])->assertRedirect(route('tipo_solicitacao.index'));

    $tipo = \App\Models\SolicitacaoTipo::where('nome', 'Declaração de vínculo')->first();

    expect($tipo)->not->toBeNull()
        ->and($tipo->exige_anexo)->toBeTrue()
        ->and($tipo->campos)->toHaveCount(2)
        ->and($tipo->campos[1]['opcoes'])->toBe(['Digital', 'Impressa']);

    $this->actingAs($admin)->get(route('tipo_solicitacao.edit', $tipo->id))->assertOk();

    $this->actingAs($admin)
        ->put(route('tipo_solicitacao.update', $tipo->id), [
            'nome' => 'Declaração de vínculo atualizada',
            'ativo' => 'N',
            'campos' => [],
        ])->assertRedirect();

    expect($tipo->fresh()->ativo)->toBeFalse()
        ->and($tipo->fresh()->campos)->toBeNull();
});

test('professor publica material na turma que leciona', function () {
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $professor = usuarioTenant($empresa, 'professor');

    Atribuicao::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'turma_id' => $ids['turma']->id, 'disciplina_id' => $ids['disciplina']->id,
        'user_id' => $professor->id, 'papel' => 'titular', 'ativo' => 'S',
    ]);

    $this->actingAs($professor)
        ->post(route('turma.material.store', $ids['turma']->id), [
            'titulo' => 'Apostila cap. 3',
            'disciplina_id' => $ids['disciplina']->id,
            'url' => 'https://exemplo.com/apostila.pdf',
        ])->assertRedirect();

    $this->assertDatabaseHas('turma_materiais', [
        'turma_id' => $ids['turma']->id, 'titulo' => 'Apostila cap. 3',
    ]);
});

test('professor nao publica material em turma que nao leciona', function () {
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $professor = usuarioTenant($empresa, 'professor');

    $this->actingAs($professor)
        ->post(route('turma.material.store', $ids['turma']->id), [
            'titulo' => 'Apostila proibida',
            'url' => 'https://exemplo.com/x.pdf',
        ])->assertForbidden();
});

test('secretaria responde solicitacao e gerencia documento pedido', function () {
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $admin = usuarioTenant($empresa, 'administrador');

    $solicitacao = \App\Models\Solicitacao::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'aluno_id' => $ids['aluno']->id, 'tipo' => 'declaracao',
        'titulo' => 'Declaração de matrícula', 'status' => 'aberta',
    ]);

    $this->actingAs($admin)
        ->put(route('solicitacao.responder', $solicitacao->id), [
            'status' => 'atendida',
            'resposta' => 'Declaração disponível na secretaria.',
        ])->assertRedirect();

    expect($solicitacao->fresh()->status)->toBe('atendida');

    $this->actingAs($admin)
        ->post(route('documento_solicitacao.store'), [
            'aluno_id' => $ids['aluno']->id,
            'tipo' => 'Comprovante de residência',
            'prazo' => now()->addDays(10)->toDateString(),
        ])->assertRedirect();

    $this->assertDatabaseHas('documento_solicitacoes', [
        'aluno_id' => $ids['aluno']->id, 'tipo' => 'Comprovante de residência', 'status' => 'pendente',
    ]);
});

test('responsavel filtra disciplinas por turma e por situacao', function () {
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $user = usuarioPortalClassroom($empresa, $ids['responsavel']);

    $this->actingAs($user)
        ->get(route('portal.disciplinas'))
        ->assertOk()
        ->assertSee('Matemática');

    $ids['anoLetivo']->update(['status' => 'encerrado']);

    $this->actingAs($user)
        ->get(route('portal.disciplinas'))
        ->assertOk()
        ->assertDontSee('Matemática');

    $this->actingAs($user)
        ->get(route('portal.filtro', 'disciplinas').'?'.http_build_query([
            'situacao_where' => 'encerradas',
            'turma_id_where' => $ids['turma']->id,
        ]))
        ->assertRedirect(route('portal.disciplinas'));

    $this->actingAs($user)
        ->get(route('portal.disciplinas'))
        ->assertOk()
        ->assertSee('Matemática');

    $this->actingAs($user)
        ->get(route('portal.filtro', 'disciplinas').'?'.http_build_query([
            'situacao_where' => 'encerradas',
            'turma_id_where' => $ids['turma']->id + 999,
        ]))
        ->assertRedirect(route('portal.disciplinas'));

    $this->actingAs($user)
        ->get(route('portal.disciplinas'))
        ->assertOk()
        ->assertSee('Nenhuma disciplina encontrada');
});

test('hub da turma mostra abas de notas e frequencia da turma', function () {
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $user = usuarioPortalClassroom($empresa, $ids['responsavel']);

    $avaliacao = \App\Models\Avaliacao::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'turma_id' => $ids['turma']->id, 'disciplina_id' => $ids['disciplina']->id,
        'tipo' => 'prova', 'nome' => 'Prova Bimestral', 'data' => now()->toDateString(),
        'user_id' => $user->id,
        'nota_maxima' => 10,
    ]);
    \App\Models\Nota::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'avaliacao_id' => $avaliacao->id, 'matricula_id' => $ids['matricula']->id,
        'nota' => 8,
    ]);

    $diario = \App\Models\Diario::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'turma_id' => $ids['turma']->id, 'disciplina_id' => $ids['disciplina']->id,
        'data' => now()->subDay()->toDateString(), 'user_id' => $user->id,
        'conteudo' => 'Aula de frações',
    ]);
    \App\Models\Frequencia::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'diario_id' => $diario->id, 'matricula_id' => $ids['matricula']->id,
        'status' => 'presente',
    ]);

    // nota de avaliacao de outra turma nao aparece no hub
    $outraTurma = Turma::create([
        'escola_id' => $ids['escola']->id, 'ano_letivo_id' => $ids['anoLetivo']->id,
        'etapa_id' => $ids['etapa']->id, 'nome' => '2º Ano B', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);
    $avaliacaoAlheia = \App\Models\Avaliacao::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'turma_id' => $outraTurma->id, 'disciplina_id' => $ids['disciplina']->id,
        'tipo' => 'trabalho', 'nome' => 'Trabalho Secreto', 'data' => now()->toDateString(),
        'user_id' => $user->id,
        'nota_maxima' => 10,
    ]);
    \App\Models\Nota::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'avaliacao_id' => $avaliacaoAlheia->id, 'matricula_id' => $ids['matricula']->id,
        'nota' => 9,
    ]);

    $this->actingAs($user)
        ->get(route('portal.turma', $ids['turma']->id))
        ->assertOk()
        ->assertSee('href="#tab-notas"', false)
        ->assertSee('href="#tab-frequencia"', false)
        ->assertSee('Prova Bimestral')
        ->assertDontSee('Trabalho Secreto')
        ->assertSee('Presença geral 100%')
        ->assertSee('80,0%');
});

test('portal turmas sinaliza aprovado e reprovado pelo corte da disciplina', function () {
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $user = usuarioPortalClassroom($empresa, $ids['responsavel']);
    $admin = usuarioTenant($empresa, 'administrador');

    // segundo aluno na mesma turma, vinculado ao mesmo responsavel
    $pessoa2 = Pessoa::create(['nome' => 'Ana Souza', 'empresa_id' => $empresa->id]);
    $aluno2 = Aluno::create([
        'pessoa_id' => $pessoa2->id, 'escola_id' => $ids['escola']->id,
        'registro_academico' => 'RA-0002', 'empresa_id' => $empresa->id,
    ]);
    $aluno2->responsaveis()->attach($ids['responsavel']->id, [
        'tipo' => 'legal', 'principal' => false, 'pode_retirar' => true, 'autoriza_contato' => true,
    ]);
    $matricula2 = Matricula::create([
        'aluno_id' => $aluno2->id, 'turma_id' => $ids['turma']->id,
        'ano_letivo_id' => $ids['anoLetivo']->id, 'status' => 'ativa',
        'numero_chamada' => 2, 'empresa_id' => $empresa->id,
    ]);

    // Maria: media 8 (aprovado) · Ana: media 4 (reprovado no padrao 7)
    foreach ([[$ids['matricula'], 8], [$matricula2, 4]] as [$mat, $nota]) {
        $av = \App\Models\Avaliacao::create([
            'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
            'turma_id' => $ids['turma']->id, 'disciplina_id' => $ids['disciplina']->id,
            'tipo' => 'prova', 'nome' => 'Prova '.fake()->unique()->numberBetween(1, 9999),
            'data' => now()->toDateString(), 'user_id' => $admin->id, 'nota_maxima' => 10,
        ]);
        \App\Models\Nota::create([
            'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
            'avaliacao_id' => $av->id, 'matricula_id' => $mat->id, 'nota' => $nota,
        ]);
    }

    $this->actingAs($user)
        ->get(route('portal.turmas'))
        ->assertOk()
        ->assertSee('Aprovado')
        ->assertSee('Reprovado');
});

test('forum de turma finalizada fica somente leitura', function () {
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $alunoUser = usuarioAlunoPortalClassroom($empresa, $ids['aluno']);
    $professor = usuarioTenant($empresa, 'professor');

    Atribuicao::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'turma_id' => $ids['turma']->id, 'disciplina_id' => $ids['disciplina']->id,
        'user_id' => $professor->id, 'papel' => 'titular', 'ativo' => 'S',
    ]);

    // topico criado enquanto o ano estava letivo
    $post = \App\Models\TurmaForumPost::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'turma_id' => $ids['turma']->id, 'disciplina_id' => $ids['disciplina']->id,
        'user_id' => $professor->id, 'mensagem' => 'Orientacao final do ano.',
    ]);

    // ano letivo encerrado: matricula segue 'ativa', mas a turma finalizou
    $ids['anoLetivo']->update(['status' => 'encerrado']);

    $this->actingAs($alunoUser)
        ->post(route('portal.turma.disciplina.forum.store', [$ids['turma']->id, $ids['disciplina']->id]), [
            'mensagem' => 'Ainda posso perguntar?',
        ])->assertForbidden();

    $this->actingAs($alunoUser)
        ->post(route('portal.turma.disciplina.forum.comentar', [$ids['turma']->id, $ids['disciplina']->id, $post->id]), [
            'mensagem' => 'Uma resposta tardia',
        ])->assertForbidden();

    $this->actingAs($professor)
        ->post(route('turma.forum.store', [$ids['turma']->id, $ids['disciplina']->id]), [
            'mensagem' => 'Aviso de fechamento',
        ])->assertForbidden();

    // historico continua acessivel e sinalizado como somente leitura
    $this->actingAs($alunoUser)
        ->get(route('portal.turma.disciplina', [$ids['turma']->id, $ids['disciplina']->id]))
        ->assertOk()
        ->assertSee('somente para consulta')
        ->assertSee('Orientacao final do ano.');
});

test('portal turmas esconde filtro aluno para conta de aluno', function () {
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $alunoUser = usuarioAlunoPortalClassroom($empresa, $ids['aluno']);
    $respUser = usuarioPortalClassroom($empresa, $ids['responsavel']);

    $this->actingAs($alunoUser)
        ->get(route('portal.turmas'))
        ->assertOk()
        ->assertDontSee('aluno_id_where', false)
        ->assertSee('situacao_where', false);

    $this->actingAs($respUser)
        ->get(route('portal.turmas'))
        ->assertOk()
        ->assertSee('aluno_id_where', false)
        ->assertSee('situacao_where', false);
});

test('portal turmas filtra por situacao do ano letivo', function () {
    $empresa = tenant();
    $ids = setupPortalClassroom($empresa);
    $user = usuarioPortalClassroom($empresa, $ids['responsavel']);

    // padrao: mostra tudo, inclusive ano encerrado
    $ids['anoLetivo']->update(['status' => 'encerrado']);
    $this->actingAs($user)->get(route('portal.turmas'))->assertOk()->assertSee('1º Ano A');

    $this->actingAs($user)
        ->get(route('portal.filtro', 'turmas').'?'.http_build_query(['situacao_where' => 'andamento']))
        ->assertRedirect(route('portal.turmas'));
    $this->actingAs($user)->get(route('portal.turmas'))
        ->assertOk()->assertSee('Nenhuma matrícula encontrada');

    $this->actingAs($user)
        ->get(route('portal.filtro', 'turmas').'?'.http_build_query(['situacao_where' => 'encerrado']))
        ->assertRedirect(route('portal.turmas'));
    $this->actingAs($user)->get(route('portal.turmas'))
        ->assertOk()->assertSee('1º Ano A');
});
