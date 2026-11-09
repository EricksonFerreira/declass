<?php

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\Atribuicao;
use App\Models\Avaliacao;
use App\Models\Diario;
use App\Models\Disciplina;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Frequencia;
use App\Models\Matricula;
use App\Models\PeriodoLetivo;
use App\Models\Pessoa;
use App\Models\Turma;
use App\Support\EmpresaContext;
use Illuminate\Support\Facades\DB;

function setupEstrutura($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Central', 'empresa_id' => $empresa->id]);
    $anoLetivo = AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => 2026, 'empresa_id' => $empresa->id,
    ]);
    $etapa = Etapa::create([
        'nome' => '1º Ano EF', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id,
    ]);
    $disciplina = Disciplina::create(['nome' => 'Matemática', 'sigla' => 'MAT', 'empresa_id' => $empresa->id]);

    return ['escola' => $escola, 'anoLetivo' => $anoLetivo, 'etapa' => $etapa, 'disciplina' => $disciplina];
}

function payloadTurma(array $ids, array $overrides = []): array
{
    return array_merge([
        'escola_id' => $ids['escola']->id,
        'ano_letivo_id' => $ids['anoLetivo']->id,
        'etapa_id' => $ids['etapa']->id,
        'nome' => '1º Ano A',
        'turno' => 'matutino',
        'sala' => '101',
        'capacidade_maxima' => 35,
        'ativo' => 'S',
    ], $overrides);
}

test('ano letivo pode ser criado e e unico por escola', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupEstrutura($empresa);
    $escola = $ids['escola'];

    $this->actingAs($admin)->get(route('ano_letivo.index'))->assertOk();

    $response = $this->actingAs($admin)->post(route('ano_letivo.store'), [
        'escola_id' => $escola->id, 'ano' => 2027, 'status' => 'planejamento', 'ativo' => 'S',
    ]);
    $response->assertSessionHasNoErrors();

    // mesmo ano na mesma escola e rejeitado
    $this->actingAs($admin)->post(route('ano_letivo.store'), [
        'escola_id' => $escola->id, 'ano' => 2027,
    ])->assertSessionHasErrors('ano');
});

test('etapa e disciplina podem ser criadas', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');

    $this->actingAs($admin)->post(route('etapa.store'), [
        'nome' => 'Educação Infantil', 'segmento' => 'infantil', 'ordem' => 1, 'ativo' => 'S',
    ])->assertSessionHasNoErrors();

    $this->actingAs($admin)->post(route('disciplina.store'), [
        'nome' => 'Português', 'sigla' => 'POR', 'ativo' => 'S',
    ])->assertSessionHasNoErrors();

    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    expect(Etapa::count())->toBe(1)->and(Disciplina::count())->toBe(1);
});

test('turma pode ser criada com escola, ano letivo e etapa validos', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupEstrutura($empresa);

    $this->actingAs($admin)->get(route('turma.index'))->assertOk();

    $response = $this->actingAs($admin)->post(route('turma.store'), payloadTurma($ids));
    $response->assertSessionHasNoErrors();

    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $turma = Turma::first();
    expect($turma)->not->toBeNull()
        ->and($turma->nome)->toBe('1º Ano A')
        ->and($turma->escola_id)->toBe($ids['escola']->id)
        ->and($turma->ano_letivo_id)->toBe($ids['anoLetivo']->id);
});

test('turma rejeita ano letivo de outra escola', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupEstrutura($empresa);
    $escolaA = $ids['escola'];
    $anoLetivoA = $ids['anoLetivo'];
    $etapa = $ids['etapa'];

    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $escolaB = Escola::create(['nome' => 'Escola B', 'empresa_id' => $empresa->id]);
    $anoLetivoB = AnoLetivo::create(['escola_id' => $escolaB->id, 'ano' => 2026, 'empresa_id' => $empresa->id]);

    // escola A + ano letivo de B => erro de consistencia
    $response = $this->actingAs($admin)->post(route('turma.store'), payloadTurma([
        'escola' => $escolaA, 'anoLetivo' => $anoLetivoB, 'etapa' => $etapa,
    ]));
    $response->assertSessionHasErrors('ano_letivo_id');
});

test('turma rejeita FKs de outro tenant', function () {
    $empresaA = tenant();
    $empresaB = tenant();
    $adminA = usuarioTenant($empresaA, 'administrador');
    $adminB = usuarioTenant($empresaB, 'administrador');

    $idsA = setupEstrutura($empresaA);
    $idsB = setupEstrutura($empresaB);

    $response = $this->actingAs($adminA)->post(route('turma.store'), payloadTurma([
        'escola' => $idsA['escola'], 'anoLetivo' => $idsA['anoLetivo'], 'etapa' => $idsB['etapa'],
    ]));
    $response->assertSessionHasErrors('etapa_id');
});

test('disciplina pode ser ofertada e removida da turma', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupEstrutura($empresa);

    $this->actingAs($admin)->post(route('turma.store'), payloadTurma($ids));
    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $turma = Turma::first();
    $disc = $ids['disciplina'];

    $this->actingAs($admin)->post(route('turma.disciplina.store', $turma->id), [
        'disciplina_id' => $disc->id, 'carga_horaria_semanal' => 5,
    ])->assertRedirect(route('turma.show', $turma->id));

    expect($turma->fresh()->disciplinas)->toHaveCount(1)
        ->and((int) $turma->fresh()->disciplinas->first()->pivot->carga_horaria_semanal)->toBe(5);

    $this->actingAs($admin)->delete(route('turma.disciplina.destroy', [$turma->id, $disc->id]));
    expect($turma->fresh()->disciplinas)->toHaveCount(0);
});

test('oferta duplicada e atualizada (upsert)', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupEstrutura($empresa);

    $this->actingAs($admin)->post(route('turma.store'), payloadTurma($ids));
    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $turma = Turma::first();
    $disc = $ids['disciplina'];

    $payload = ['disciplina_id' => $disc->id, 'carga_horaria_semanal' => 3];
    $this->actingAs($admin)->post(route('turma.disciplina.store', $turma->id), $payload);
    $this->actingAs($admin)->post(route('turma.disciplina.store', $turma->id), ['disciplina_id' => $disc->id, 'carga_horaria_semanal' => 8]);

    expect(DB::table('turma_disciplina')->count())->toBe(1)
        ->and((int) $turma->fresh()->disciplinas->first()->pivot->carga_horaria_semanal)->toBe(8);
});

test('oferta de disciplina aceita media de aprovacao customizada', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupEstrutura($empresa);

    $this->actingAs($admin)->post(route('turma.store'), payloadTurma($ids));
    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $turma = Turma::first();
    $disc = $ids['disciplina'];

    $this->actingAs($admin)->post(route('turma.disciplina.store', $turma->id), [
        'disciplina_id' => $disc->id, 'media_aprovacao' => 6.5,
    ])->assertRedirect(route('turma.show', $turma->id));

    $oferta = $turma->fresh()->disciplinas->first();
    expect((float) $oferta->pivot->media_aprovacao)->toBe(6.5)
        ->and($oferta->mediaAprovacaoNaTurma())->toBe(6.5);

    $this->actingAs($admin)->put(route('turma.disciplina.update', [$turma->id, $disc->id]), [
        'carga_horaria_semanal' => 4, 'media_aprovacao' => 5,
    ]);

    $oferta = $turma->fresh()->disciplinas->first();
    expect((float) $oferta->pivot->media_aprovacao)->toBe(5.0)
        ->and((int) $oferta->pivot->carga_horaria_semanal)->toBe(4);

    // vazio volta ao padrao do sistema
    $this->actingAs($admin)->put(route('turma.disciplina.update', [$turma->id, $disc->id]), [
        'carga_horaria_semanal' => 4,
    ]);

    expect($turma->fresh()->disciplinas->first()->mediaAprovacaoNaTurma())->toBe(7.0);
});

test('professor pode ser atribuido a turma', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $prof = usuarioTenant($empresa, 'professor');
    $ids = setupEstrutura($empresa);

    $this->actingAs($admin)->post(route('turma.store'), payloadTurma($ids));
    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $turma = Turma::first();
    $disc = $ids['disciplina'];
    $this->actingAs($admin)->post(route('turma.disciplina.store', $turma->id), ['disciplina_id' => $disc->id]);

    $response = $this->actingAs($admin)->post(route('turma.atribuicao.store', $turma->id), [
        'user_id' => $prof->id, 'disciplina_id' => $disc->id, 'papel' => 'titular',
    ]);
    $response->assertRedirect(route('turma.show', $turma->id));

    expect(Atribuicao::count())->toBe(1)
        ->and(Atribuicao::first()->user_id)->toBe($prof->id);
});

test('atribuicao rejeita usuario de outro tenant', function () {
    $empresaA = tenant();
    $empresaB = tenant();
    $adminA = usuarioTenant($empresaA, 'administrador');
    $profB = usuarioTenant($empresaB, 'professor');
    $ids = setupEstrutura($empresaA);

    $this->actingAs($adminA)->post(route('turma.store'), payloadTurma($ids));
    app(EmpresaContext::class)->setEmpresaId($empresaA->id);
    $turma = Turma::first();

    $this->actingAs($adminA)->post(route('turma.atribuicao.store', $turma->id), [
        'user_id' => $profB->id, 'papel' => 'titular',
    ])->assertSessionHasErrors('user_id');
});

test('professor ve apenas turmas que leciona e nao gerencia estrutura', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $prof = usuarioTenant($empresa, 'professor');
    $ids = setupEstrutura($empresa);

    $this->actingAs($admin)->post(route('turma.store'), payloadTurma($ids));
    $this->actingAs($admin)->post(route('turma.store'), payloadTurma($ids, ['nome' => '2º Ano B', 'sala' => '202']));
    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $turma = Turma::where('nome', '1º Ano A')->first();

    // sem atribuicao: index vazio, show bloqueado
    $this->actingAs($prof)->get(route('turma.index'))->assertOk();
    expect($this->actingAs($prof)->get(route('turma.index'))->viewData('turmas')->total())->toBe(0);
    $this->actingAs($prof)->get(route('turma.show', $turma->id))->assertForbidden();
    $this->actingAs($prof)->get(route('turma.create'))->assertForbidden();
    $this->actingAs($prof)->post(route('turma.store'), payloadTurma($ids))->assertForbidden();
    $this->actingAs($prof)->post(route('etapa.store'), ['nome' => 'X', 'segmento' => 'outro'])->assertForbidden();

    // com atribuicao ativa: show liberado
    \App\Models\Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $turma->id,
        'user_id' => $prof->id, 'papel' => 'titular',
    ]);

    $this->actingAs($prof)->get(route('turma.show', $turma->id))->assertOk();

    // index lista apenas a turma lecionada (a outra fica de fora)
    $response = $this->actingAs($prof)->get(route('turma.index'));
    $turmasVisiveis = $response->viewData('turmas');
    expect($turmasVisiveis->total())->toBe(1)
        ->and($turmasVisiveis->first()->id)->toBe($turma->id);
});

test('turma show reune diario, avaliacoes e presencas da turma', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $prof = usuarioTenant($empresa, 'professor');
    $ids = setupEstrutura($empresa);

    $turma = Turma::create(payloadTurma($ids) + ['empresa_id' => $empresa->id]);
    $turma2 = Turma::create(payloadTurma($ids, ['nome' => '2º Ano B']) + ['empresa_id' => $empresa->id]);

    DB::table('turma_disciplina')->insert([
        'turma_id' => $turma->id, 'disciplina_id' => $ids['disciplina']->id,
        'carga_horaria_semanal' => 5, 'created_at' => now(), 'updated_at' => now(),
    ]);

    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $turma->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $prof->id,
        'papel' => 'titular',
    ]);

    $pessoa = Pessoa::create(['nome' => 'Ana Souza', 'empresa_id' => $empresa->id]);
    $aluno = Aluno::create([
        'pessoa_id' => $pessoa->id, 'escola_id' => $ids['escola']->id,
        'registro_academico' => 'RA-1', 'empresa_id' => $empresa->id,
    ]);
    $matricula = Matricula::create([
        'aluno_id' => $aluno->id, 'turma_id' => $turma->id,
        'ano_letivo_id' => $ids['anoLetivo']->id, 'status' => 'ativa',
        'numero_chamada' => 1, 'empresa_id' => $empresa->id,
    ]);

    // Diario + chamada na turma do professor.
    $diario = Diario::create([
        'empresa_id' => $empresa->id, 'turma_id' => $turma->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $prof->id,
        'data' => '2026-03-10', 'conteudo' => 'Frações',
    ]);
    Frequencia::create([
        'empresa_id' => $empresa->id, 'diario_id' => $diario->id,
        'matricula_id' => $matricula->id, 'status' => 'presente',
    ]);
    $diarioFalta = Diario::create([
        'empresa_id' => $empresa->id, 'turma_id' => $turma->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $prof->id,
        'data' => '2026-03-11', 'conteudo' => 'Equações',
    ]);
    Frequencia::create([
        'empresa_id' => $empresa->id, 'diario_id' => $diarioFalta->id,
        'matricula_id' => $matricula->id, 'status' => 'falta',
    ]);

    // Registros de outra turma nao devem aparecer.
    Diario::create([
        'empresa_id' => $empresa->id, 'turma_id' => $turma2->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $admin->id,
        'data' => '2026-03-10', 'conteudo' => 'Aula de outra turma',
    ]);
    $avaliacao = Avaliacao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $turma->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $prof->id,
        'tipo' => 'prova', 'nome' => 'Prova Bimestral', 'periodo' => '1º Bimestre',
        'data' => '2026-03-20', 'nota_maxima' => 10,
    ]);
    Avaliacao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $turma2->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $admin->id,
        'tipo' => 'prova', 'nome' => 'Prova de outra turma', 'periodo' => '1º Bimestre',
        'data' => '2026-03-20', 'nota_maxima' => 10,
    ]);

    $response = $this->actingAs($prof)->get(route('turma.show', $turma->id));
    $response->assertOk();

    expect($response->viewData('registros')->map(fn ($r) => $r['model']->id)->all())
        ->toBe([$avaliacao->id, $diarioFalta->id, $diario->id]);

    $presencas = $response->viewData('presencas');
    expect((int) $presencas[$matricula->id]->total)->toBe(2)
        ->and((int) $presencas[$matricula->id]->presente)->toBe(1)
        ->and((int) $presencas[$matricula->id]->falta)->toBe(1);
});

test('turma show mostra acoes pedagogicas ao professor e esconde gestao', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $prof = usuarioTenant($empresa, 'professor');
    $ids = setupEstrutura($empresa);

    $outraDisciplina = Disciplina::create(['nome' => 'História', 'sigla' => 'HIS', 'empresa_id' => $empresa->id]);

    $turma = Turma::create(payloadTurma($ids) + ['empresa_id' => $empresa->id]);
    DB::table('turma_disciplina')->insert([
        ['turma_id' => $turma->id, 'disciplina_id' => $ids['disciplina']->id, 'carga_horaria_semanal' => 5, 'created_at' => now(), 'updated_at' => now()],
        ['turma_id' => $turma->id, 'disciplina_id' => $outraDisciplina->id, 'carga_horaria_semanal' => 3, 'created_at' => now(), 'updated_at' => now()],
    ]);

    // Atribuicao por disciplina: professor ve apenas a sua.
    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $turma->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $prof->id, 'papel' => 'titular',
    ]);
    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $turma->id,
        'disciplina_id' => $outraDisciplina->id, 'user_id' => $admin->id, 'papel' => 'titular',
    ]);

    // Diario de hoje habilita o atalho "Chamada de hoje".
    $diarioHoje = Diario::create([
        'empresa_id' => $empresa->id, 'turma_id' => $turma->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $prof->id,
        'data' => now()->toDateString(), 'conteudo' => 'Aula de hoje',
    ]);

    // Registros de OUTRO professor na mesma turma: nao devem aparecer.
    Diario::create([
        'empresa_id' => $empresa->id, 'turma_id' => $turma->id,
        'disciplina_id' => $outraDisciplina->id, 'user_id' => $admin->id,
        'data' => now()->toDateString(), 'conteudo' => 'Aula de outro professor',
    ]);
    Avaliacao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $turma->id,
        'disciplina_id' => $outraDisciplina->id, 'user_id' => $admin->id,
        'tipo' => 'prova', 'nome' => 'Prova de História', 'periodo' => '1º Bimestre',
        'data' => now()->toDateString(), 'nota_maxima' => 10,
    ]);

    $response = $this->actingAs($prof)->get(route('turma.show', $turma->id));
    $response->assertOk();

    // Botoes com a turma pre-selecionada + grade horaria (somente leitura).
    $response->assertSee('turma_id='.$turma->id);
    $response->assertSee(route('turma.grade', $turma->id));
    $response->assertSee('Chamada de hoje');
    expect($response->viewData('diarioHoje')->id)->toBe($diarioHoje->id);

    // Escopo por professor: so a disciplina dele, diario/avaliacao so dele.
    expect($response->viewData('disciplinasVisiveis')->pluck('id')->all())
        ->toBe([$ids['disciplina']->id])
        ->and($response->viewData('registros')->map(fn ($r) => $r['model']->id)->all())
        ->toBe([$diarioHoje->id]);
    $response->assertDontSee('Aula de outro professor');
    $response->assertDontSee('Prova de História');

    // Sem acoes nem dados administrativos para o professor.
    $response->assertDontSee('Nova matrícula');
    $response->assertDontSee('Ofertar disciplina');
    $response->assertDontSee('Professores atribuídos');
});

test('turma chamada abre chamada de hoje ou leva ao registro da aula', function () {
    $empresa = tenant();
    usuarioTenant($empresa, 'administrador');
    $prof = usuarioTenant($empresa, 'professor');
    $ids = setupEstrutura($empresa);

    $turma = Turma::create(payloadTurma($ids) + ['empresa_id' => $empresa->id]);
    DB::table('turma_disciplina')->insert([
        'turma_id' => $turma->id, 'disciplina_id' => $ids['disciplina']->id,
        'carga_horaria_semanal' => 5, 'created_at' => now(), 'updated_at' => now(),
    ]);
    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $turma->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $prof->id, 'papel' => 'titular',
        'ativo' => 'S',
    ]);

    // Sem disciplina: vai direto para a chamada unificada, que
    // resolve a unica disciplina do professor automaticamente.
    $this->actingAs($prof)->get(route('turma.chamada', $turma->id))
        ->assertRedirect(route('diario.chamada', [
            'turma_id' => $turma->id,
            'data' => now()->toDateString(),
        ]));

    // Com disciplina escolhida: vai para a chamada unificada
    // (aula + presenca numa tela so), exista diario ou nao.
    $this->actingAs($prof)->get(route('turma.chamada', $turma->id).'?disciplina_id='.$ids['disciplina']->id)
        ->assertRedirect(route('diario.chamada', [
            'turma_id' => $turma->id,
            'disciplina_id' => $ids['disciplina']->id,
            'data' => now()->toDateString(),
        ]));

    // Com aula hoje: mesma chamada unificada, agora pre-preenchida.
    $diario = Diario::create([
        'empresa_id' => $empresa->id, 'turma_id' => $turma->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $prof->id,
        'data' => now()->toDateString(), 'conteudo' => 'Aula de hoje',
    ]);
    $this->actingAs($prof)->get(route('turma.chamada', $turma->id).'?disciplina_id='.$ids['disciplina']->id)
        ->assertRedirect(route('diario.chamada', [
            'turma_id' => $turma->id,
            'disciplina_id' => $ids['disciplina']->id,
            'data' => now()->toDateString(),
        ]));

    // Professor sem atribuicao na turma: bloqueado.
    $profFora = usuarioTenant($empresa, 'professor');
    $this->actingAs($profFora)->get(route('turma.chamada', $turma->id))->assertForbidden();
});

test('chamada unificada grava aula, conteudo e presenca numa tela so', function () {
    $empresa = tenant();
    usuarioTenant($empresa, 'administrador');
    $prof = usuarioTenant($empresa, 'professor');
    $ids = setupEstrutura($empresa);

    $turma = Turma::create(payloadTurma($ids) + ['empresa_id' => $empresa->id]);
    DB::table('turma_disciplina')->insert([
        'turma_id' => $turma->id, 'disciplina_id' => $ids['disciplina']->id,
        'carga_horaria_semanal' => 5, 'created_at' => now(), 'updated_at' => now(),
    ]);
    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $turma->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $prof->id, 'papel' => 'titular',
        'ativo' => 'S',
    ]);

    $pessoa = Pessoa::create(['nome' => 'Bia Lima', 'empresa_id' => $empresa->id]);
    $aluno = Aluno::create([
        'pessoa_id' => $pessoa->id, 'escola_id' => $ids['escola']->id,
        'registro_academico' => 'RA-9', 'empresa_id' => $empresa->id,
    ]);
    $matricula = Matricula::create([
        'aluno_id' => $aluno->id, 'turma_id' => $turma->id,
        'ano_letivo_id' => $ids['anoLetivo']->id, 'status' => 'ativa',
        'numero_chamada' => 1, 'empresa_id' => $empresa->id,
    ]);

    $data = now()->toDateString();
    $query = http_build_query([
        'turma_id' => $turma->id, 'disciplina_id' => $ids['disciplina']->id, 'data' => $data,
    ]);

    // GET: tela unica com campos de aula + alunos matriculados.
    $this->actingAs($prof)->get(route('diario.chamada').'?'.$query)
        ->assertOk()
        ->assertSee('Conteúdo ministrado')
        ->assertSee('Observações')
        ->assertSee('Bia Lima');

    // POST: cria o diario e grava a presenca de uma vez.
    $this->actingAs($prof)->post(route('diario.chamada.salvar'), [
        'turma_id' => $turma->id, 'disciplina_id' => $ids['disciplina']->id, 'data' => $data,
        'conteudo' => 'Equações do 2º grau', 'observacoes' => 'Turma participativa',
        'frequencias' => [
            ['matricula_id' => $matricula->id, 'status' => 'falta'],
        ],
    ])->assertRedirect(route('turma.show', $turma->id).'#tab-registros');

    $diario = Diario::where('turma_id', $turma->id)->where('disciplina_id', $ids['disciplina']->id)->firstOrFail();
    expect($diario->conteudo)->toBe('Equações do 2º grau')
        ->and($diario->observacoes)->toBe('Turma participativa')
        ->and(Frequencia::where('diario_id', $diario->id)->where('matricula_id', $matricula->id)->value('status'))->toBe('falta');

    // Segundo POST na mesma combinacao: atualiza, nao duplica diario.
    $this->actingAs($prof)->post(route('diario.chamada.salvar'), [
        'turma_id' => $turma->id, 'disciplina_id' => $ids['disciplina']->id, 'data' => $data,
        'conteudo' => 'Equações (revisão)', 'observacoes' => null,
        'frequencias' => [
            ['matricula_id' => $matricula->id, 'status' => 'presente'],
        ],
    ])->assertRedirect();

    expect(Diario::where('turma_id', $turma->id)->where('disciplina_id', $ids['disciplina']->id)->count())->toBe(1)
        ->and($diario->fresh()->conteudo)->toBe('Equações (revisão)')
        ->and(Frequencia::where('diario_id', $diario->id)->value('status'))->toBe('presente');
});

test('professor ve so turmas em periodo letivo e filtra as fora do periodo', function () {
    $empresa = tenant();
    usuarioTenant($empresa, 'administrador');
    $prof = usuarioTenant($empresa, 'professor');
    $ids = setupEstrutura($empresa);

    // Ano letivo atual com periodo cobrindo hoje + ano anterior encerrado.
    PeriodoLetivo::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'ano_letivo_id' => $ids['anoLetivo']->id, 'nome' => '1º Bimestre', 'ordem' => 1,
        'data_inicio' => now()->subMonth()->toDateString(),
        'data_fim' => now()->addMonth()->toDateString(), 'ativo' => 'S',
    ]);
    $anoPassado = AnoLetivo::create([
        'escola_id' => $ids['escola']->id, 'ano' => 2025,
        'status' => 'encerrado', 'empresa_id' => $empresa->id,
    ]);

    $turmaAtual = Turma::create(payloadTurma($ids) + ['empresa_id' => $empresa->id]);
    $turmaAntiga = Turma::create(payloadTurma($ids, [
        'nome' => '1º Ano B', 'ano_letivo_id' => $anoPassado->id,
    ]) + ['empresa_id' => $empresa->id]);

    foreach ([$turmaAtual, $turmaAntiga] as $turma) {
        Atribuicao::create([
            'empresa_id' => $empresa->id, 'turma_id' => $turma->id,
            'user_id' => $prof->id, 'papel' => 'titular',
        ]);
    }

    // Padrao: so a turma em periodo letivo aparece.
    $response = $this->actingAs($prof)->get(route('turma.index'));
    $turmas = $response->viewData('turmas');
    expect($turmas->pluck('id')->all())->toBe([$turmaAtual->id]);

    // Filtro "fora do periodo" mostra a turma do ano encerrado.
    $this->actingAs($prof)->get(route('turma.filtro', ['periodo_letivo' => 'fora']));
    $turmas = $this->actingAs($prof)->get(route('turma.index'))->viewData('turmas');
    expect($turmas->pluck('id')->all())->toBe([$turmaAntiga->id]);

    // "Todas" mostra as duas.
    $this->actingAs($prof)->get(route('turma.filtro', ['periodo_letivo' => 'todas']));
    $turmas = $this->actingAs($prof)->get(route('turma.index'))->viewData('turmas');
    expect($turmas->pluck('id')->all())->toBe([$turmaAtual->id, $turmaAntiga->id]);
});

test('professor com ano letivo sem periodos ve a turma (ano letivo ativo)', function () {
    $empresa = tenant();
    usuarioTenant($empresa, 'administrador');
    $prof = usuarioTenant($empresa, 'professor');
    $ids = setupEstrutura($empresa); // ano letivo 'letivo' sem periodos

    $turma = Turma::create(payloadTurma($ids) + ['empresa_id' => $empresa->id]);
    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $turma->id,
        'user_id' => $prof->id, 'papel' => 'titular',
    ]);

    $turmas = $this->actingAs($prof)->get(route('turma.index'))->viewData('turmas');
    expect($turmas->pluck('id')->all())->toBe([$turma->id]);
});

test('turmas tem escopo de tenant', function () {
    $empresaA = tenant();
    $empresaB = tenant();
    $idsA = setupEstrutura($empresaA);
    $idsB = setupEstrutura($empresaB);

    app(EmpresaContext::class)->setEmpresaId($empresaA->id);
    Turma::create(payloadTurma($idsA) + ['empresa_id' => $empresaA->id]);

    app(EmpresaContext::class)->setEmpresaId($empresaB->id);
    Turma::create(payloadTurma($idsB) + ['empresa_id' => $empresaB->id]);

    app(EmpresaContext::class)->setEmpresaId($empresaA->id);
    expect(Turma::count())->toBe(1)
        ->and(Turma::first()->nome)->toBe('1º Ano A');
});
