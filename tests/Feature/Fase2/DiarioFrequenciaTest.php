<?php

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\Atribuicao;
use App\Models\Diario;
use App\Models\Disciplina;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Frequencia;
use App\Models\Matricula;
use App\Models\Pessoa;
use App\Models\Turma;
use App\Support\EmpresaContext;
use Illuminate\Support\Facades\DB;

function setupDiario($empresa, ?int $userId = null): array
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

    // oferta da disciplina na turma
    DB::table('turma_disciplina')->insert([
        'turma_id' => $turma->id,
        'disciplina_id' => $disciplina->id,
        'carga_horaria_semanal' => 5,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // aluno matriculado
    $pessoa = Pessoa::create(['nome' => 'Maria Silva', 'empresa_id' => $empresa->id]);
    $aluno = Aluno::create([
        'pessoa_id' => $pessoa->id, 'escola_id' => $escola->id,
        'registro_academico' => 'RA-0001', 'empresa_id' => $empresa->id,
    ]);
    $matricula = Matricula::create([
        'aluno_id' => $aluno->id, 'turma_id' => $turma->id,
        'ano_letivo_id' => $anoLetivo->id, 'status' => 'ativa',
        'numero_chamada' => 1, 'empresa_id' => $empresa->id,
    ]);

    return compact('escola', 'anoLetivo', 'etapa', 'turma', 'disciplina', 'pessoa', 'aluno', 'matricula');
}

function payloadDiario(array $ids, array $overrides = []): array
{
    return array_merge([
        'turma_id' => $ids['turma']->id,
        'disciplina_id' => $ids['disciplina']->id,
        'data' => '2026-03-10',
        'conteudo' => 'Frações equivalentes',
    ], $overrides);
}

test('professor pode registrar aula e fazer chamada', function () {
    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');
    $ids = setupDiario($empresa);

    // professor precisa de atribuicao para ver a turma no formulario
    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $ids['turma']->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $professor->id,
        'papel' => 'titular',
    ]);

    $this->actingAs($professor)->get(route('diario.index'))->assertOk();

    $this->actingAs($professor)->post(route('diario.store'), payloadDiario($ids))
        ->assertSessionHasNoErrors();

    $diario = Diario::first();
    expect($diario)->not->toBeNull()
        ->and($diario->user_id)->toBe($professor->id)
        ->and($diario->empresa_id)->toBe($empresa->id);

    // chamada em lote
    $this->actingAs($professor)->put(route('diario.frequencia.salvar', $diario->id), [
        'frequencias' => [
            ['matricula_id' => $ids['matricula']->id, 'status' => 'presente'],
        ],
    ])->assertSessionHasNoErrors();

    expect(Frequencia::count())->toBe(1)
        ->and(Frequencia::first()->status)->toBe('presente');
});

test('aula rejeita disciplina nao ofertada na turma', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupDiario($empresa);

    $outra = Disciplina::create(['nome' => 'História', 'empresa_id' => $empresa->id]);

    $this->actingAs($admin)->post(route('diario.store'), payloadDiario($ids, [
        'disciplina_id' => $outra->id,
    ]))->assertSessionHasErrors('disciplina_id');
});

test('aula duplicada turma+disciplina+data e rejeitada', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupDiario($empresa);

    $this->actingAs($admin)->post(route('diario.store'), payloadDiario($ids));

    $this->actingAs($admin)->post(route('diario.store'), payloadDiario($ids, [
        'conteudo' => 'Outro conteudo',
    ]))->assertSessionHasErrors('data');
});

test('chamada rejeita matricula de outra turma', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupDiario($empresa);

    // segunda turma + matricula de outro aluno
    $turma2 = Turma::create([
        'escola_id' => $ids['escola']->id, 'ano_letivo_id' => $ids['anoLetivo']->id,
        'etapa_id' => $ids['etapa']->id, 'nome' => '2º Ano A', 'turno' => 'vespertino',
        'empresa_id' => $empresa->id,
    ]);
    $pessoa2 = Pessoa::create(['nome' => 'João', 'empresa_id' => $empresa->id]);
    $aluno2 = Aluno::create([
        'pessoa_id' => $pessoa2->id, 'escola_id' => $ids['escola']->id,
        'registro_academico' => 'RA-0002', 'empresa_id' => $empresa->id,
    ]);
    $matricula2 = Matricula::create([
        'aluno_id' => $aluno2->id, 'turma_id' => $turma2->id,
        'ano_letivo_id' => $ids['anoLetivo']->id, 'status' => 'ativa',
        'empresa_id' => $empresa->id,
    ]);

    $this->actingAs($admin)->post(route('diario.store'), payloadDiario($ids));
    $diario = Diario::first();

    $this->actingAs($admin)->put(route('diario.frequencia.salvar', $diario->id), [
        'frequencias' => [
            ['matricula_id' => $matricula2->id, 'status' => 'presente'],
        ],
    ])->assertSessionHasErrors('frequencias');

    expect(Frequencia::count())->toBe(0);
});

test('professor nao edita diario de outro professor', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $professorB = usuarioTenant($empresa, 'professor');
    $ids = setupDiario($empresa);

    $this->actingAs($admin)->post(route('diario.store'), payloadDiario($ids));
    $diario = Diario::first();

    $this->actingAs($professorB)->get(route('diario.edit', $diario->id))->assertForbidden();
    $this->actingAs($professorB)->put(route('diario.update', $diario->id), [
        'data' => '2026-03-11', 'conteudo' => 'Tentativa',
    ])->assertForbidden();
});

test('registrar aula vindo da chamada abre a chamada ao salvar', function () {
    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');
    $ids = setupDiario($empresa);

    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $ids['turma']->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $professor->id,
        'papel' => 'titular',
    ]);

    $this->actingAs($professor)->post(route('diario.store'), payloadDiario($ids) + ['ir_para_chamada' => '1'])
        ->assertRedirect(route('diario.frequencia', Diario::latest('id')->first()->id));
});

test('professor nao gerencia diario de outro professor na mesma turma', function () {
    $empresa = tenant();
    usuarioTenant($empresa, 'administrador');
    $profA = usuarioTenant($empresa, 'professor');
    $profB = usuarioTenant($empresa, 'professor');
    $regente = usuarioTenant($empresa, 'professor');
    $ids = setupDiario($empresa);

    $outra = Disciplina::create(['nome' => 'História', 'sigla' => 'HIS', 'empresa_id' => $empresa->id]);
    DB::table('turma_disciplina')->insert([
        'turma_id' => $ids['turma']->id, 'disciplina_id' => $outra->id,
        'carga_horaria_semanal' => 3, 'created_at' => now(), 'updated_at' => now(),
    ]);

    // profA e profB lecionam disciplinas diferentes na mesma turma.
    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $ids['turma']->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $profA->id, 'papel' => 'titular',
    ]);
    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $ids['turma']->id,
        'disciplina_id' => $outra->id, 'user_id' => $profB->id, 'papel' => 'titular',
    ]);
    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $ids['turma']->id,
        'disciplina_id' => null, 'user_id' => $regente->id, 'papel' => 'titular',
    ]);

    $diario = Diario::create(payloadDiario($ids, [
        'user_id' => $profA->id, 'empresa_id' => $empresa->id,
    ]));

    // profB: mesma turma, mas diario alheio -> bloqueado.
    $this->actingAs($profB)->get(route('diario.edit', $diario->id))->assertForbidden();
    $this->actingAs($profB)->get(route('diario.frequencia', $diario->id))->assertForbidden();

    // regente da turma: liberado.
    $this->actingAs($regente)->get(route('diario.edit', $diario->id))->assertOk();
    $this->actingAs($regente)->get(route('diario.frequencia', $diario->id))->assertOk();
});

test('professor ve diarios das turmas que leciona', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $professor = usuarioTenant($empresa, 'professor');
    $ids = setupDiario($empresa);

    // professor precisa de atribuicao ativa na turma
    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $ids['turma']->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $professor->id,
        'papel' => 'titular',
    ]);

    // diario do admin na mesma turma
    $this->actingAs($admin)->post(route('diario.store'), payloadDiario($ids));

    // diario do professor (disciplina precisa estar ofertada — usa a mesma)
    $this->actingAs($professor)->post(route('diario.store'), payloadDiario($ids, [
        'data' => '2026-03-11', 'conteudo' => 'Aula do professor',
    ]));

    expect(Diario::count())->toBe(2);

    // professor ve apenas o proprio diario, nao o do admin na mesma turma
    $response = $this->actingAs($professor)->get(route('diario.index'));
    $response->assertOk();
    $diariosVisiveis = $response->viewData('diarios');
    expect($diariosVisiveis->total())->toBe(1);
    expect($diariosVisiveis->getCollection()->first()->conteudo)->toBe('Aula do professor');

    // admin ve ambos
    $responseAdmin = $this->actingAs($admin)->get(route('diario.index'));
    expect($responseAdmin->viewData('diarios')->total())->toBe(2);
});

test('professor regente ve todos os diarios das turmas que rege', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $regente = usuarioTenant($empresa, 'professor');
    $ids = setupDiario($empresa);

    // atribuicao sem disciplina = regente da turma
    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $ids['turma']->id,
        'disciplina_id' => null, 'user_id' => $regente->id,
        'papel' => 'titular',
    ]);

    // diario do admin e do regente na mesma turma
    $this->actingAs($admin)->post(route('diario.store'), payloadDiario($ids));
    $this->actingAs($regente)->post(route('diario.store'), payloadDiario($ids, [
        'data' => '2026-03-11', 'conteudo' => 'Aula do regente',
    ]));

    expect(Diario::count())->toBe(2);

    // regente ve os dois diarios da turma
    $response = $this->actingAs($regente)->get(route('diario.index'));
    expect($response->viewData('diarios')->total())->toBe(2);
});

test('professor nao registra aula em turma que nao leciona', function () {
    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');
    $ids = setupDiario($empresa);

    // sem atribuicao: store rejeitado e index vazio
    $this->actingAs($professor)->post(route('diario.store'), payloadDiario($ids))
        ->assertSessionHasErrors('turma_id');
    expect(Diario::count())->toBe(0);

    $response = $this->actingAs($professor)->get(route('diario.index'));
    expect($response->viewData('diarios')->total())->toBe(0);
});

test('professor ve apenas suas turmas, disciplinas e o proprio nome nos filtros', function () {
    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');
    $outroProfessor = usuarioTenant($empresa, 'professor', ['nome' => 'Professor Outro']);
    $ids = setupDiario($empresa);

    // Segunda turma + disciplina sem atribuicao para o professor.
    $turma2 = Turma::create([
        'escola_id' => $ids['escola']->id, 'ano_letivo_id' => $ids['anoLetivo']->id,
        'etapa_id' => $ids['etapa']->id, 'nome' => '2º Ano B', 'turno' => 'vespertino',
        'empresa_id' => $empresa->id,
    ]);
    $disciplina2 = Disciplina::create(['nome' => 'Geografia', 'empresa_id' => $empresa->id]);

    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $ids['turma']->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $professor->id,
        'papel' => 'titular',
    ]);

    $response = $this->actingAs($professor)->get(route('diario.index'));
    $response->assertOk();

    $filters = collect($response->viewData('filters'));
    $additional = collect($response->viewData('additionalFilters'));

    $turmaOptions = $filters->firstWhere('name', 'turma_id')['options'];
    $disciplinaOptions = $filters->firstWhere('name', 'disciplina_id')['options'];
    $profOptions = $additional->firstWhere('name', 'user_id')['options'];

    expect($turmaOptions)->toHaveCount(1)->toHaveKey($ids['turma']->id)
        ->and($turmaOptions)->not->toHaveKey($turma2->id)
        ->and($disciplinaOptions)->toHaveCount(1)->toHaveKey($ids['disciplina']->id)
        ->and($disciplinaOptions)->not->toHaveKey($disciplina2->id)
        ->and($profOptions)->toBe([$professor->id => $professor->nome])
        ->and($profOptions)->not->toHaveKey($outroProfessor->id);
});

test('professor regente ve todas as disciplinas ofertadas na turma', function () {
    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');
    $ids = setupDiario($empresa);

    // Segunda disciplina ofertada na mesma turma.
    $disciplina2 = Disciplina::create(['nome' => 'Português', 'empresa_id' => $empresa->id]);
    DB::table('turma_disciplina')->insert([
        'turma_id' => $ids['turma']->id, 'disciplina_id' => $disciplina2->id,
        'carga_horaria_semanal' => 5, 'created_at' => now(), 'updated_at' => now(),
    ]);
    // Disciplina sem oferta em nenhuma turma do professor.
    $disciplina3 = Disciplina::create(['nome' => 'Artes', 'empresa_id' => $empresa->id]);

    // Atribuicao sem disciplina = regente da turma.
    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $ids['turma']->id,
        'user_id' => $professor->id, 'papel' => 'titular',
    ]);

    $response = $this->actingAs($professor)->get(route('diario.index'));
    $disciplinaOptions = collect($response->viewData('filters'))
        ->firstWhere('name', 'disciplina_id')['options'];

    expect($disciplinaOptions)->toHaveKey($ids['disciplina']->id)
        ->and($disciplinaOptions)->toHaveKey($disciplina2->id)
        ->and($disciplinaOptions)->not->toHaveKey($disciplina3->id);

    // Regente pode registrar aula em qualquer disciplina ofertada.
    $this->actingAs($professor)->post(route('diario.store'), payloadDiario($ids, [
        'disciplina_id' => $disciplina2->id,
    ]))->assertSessionHasNoErrors();
});

test('professor nao registra aula em disciplina que nao leciona', function () {
    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');
    $ids = setupDiario($empresa);

    $disciplina2 = Disciplina::create(['nome' => 'Português', 'empresa_id' => $empresa->id]);
    DB::table('turma_disciplina')->insert([
        'turma_id' => $ids['turma']->id, 'disciplina_id' => $disciplina2->id,
        'carga_horaria_semanal' => 5, 'created_at' => now(), 'updated_at' => now(),
    ]);

    // Atribuicao somente para Matematica — Portugues ofertada mas nao atribuida.
    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $ids['turma']->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $professor->id,
        'papel' => 'titular',
    ]);

    $this->actingAs($professor)->post(route('diario.store'), payloadDiario($ids, [
        'disciplina_id' => $disciplina2->id,
    ]))->assertSessionHasErrors('disciplina_id');

    expect(Diario::count())->toBe(0);
});

test('diarios tem escopo de tenant', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupDiario($empresa);

    $this->actingAs($admin)->post(route('diario.store'), payloadDiario($ids));
    expect(Diario::count())->toBe(1);

    app(EmpresaContext::class)->setEmpresaId(tenant()->id);
    expect(Diario::count())->toBe(0);
});

test('chamada e upsert: segunda gravacao atualiza status', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupDiario($empresa);

    $this->actingAs($admin)->post(route('diario.store'), payloadDiario($ids));
    $diario = Diario::first();

    $payload = ['frequencias' => [
        ['matricula_id' => $ids['matricula']->id, 'status' => 'falta', 'justificativa' => null],
    ]];

    $this->actingAs($admin)->put(route('diario.frequencia.salvar', $diario->id), $payload);
    $this->actingAs($admin)->put(route('diario.frequencia.salvar', $diario->id), [
        'frequencias' => [
            ['matricula_id' => $ids['matricula']->id, 'status' => 'justificada', 'justificativa' => 'Atestado'],
        ],
    ]);

    expect(Frequencia::count())->toBe(1)
        ->and(Frequencia::first()->status)->toBe('justificada')
        ->and(Frequencia::first()->justificativa)->toBe('Atestado');
});

test('professor nao grava justificativa livre na chamada', function () {
    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');
    $ids = setupDiario($empresa);

    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $ids['turma']->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $professor->id,
        'papel' => 'titular',
    ]);

    $this->actingAs($professor)->post(route('diario.store'), payloadDiario($ids));
    $diario = Diario::first();

    // tentativa forjada de enviar justificativa
    $this->actingAs($professor)->put(route('diario.frequencia.salvar', $diario->id), [
        'frequencias' => [
            ['matricula_id' => $ids['matricula']->id, 'status' => 'falta', 'justificativa' => 'Texto forjado'],
        ],
    ])->assertSessionHasNoErrors();

    expect(Frequencia::first()->status)->toBe('falta')
        ->and(Frequencia::first()->justificativa)->toBeNull();
});

test('professor nao forja status justificada ou abonada na chamada', function () {
    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');
    $ids = setupDiario($empresa);

    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $ids['turma']->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $professor->id,
        'papel' => 'titular',
    ]);

    $this->actingAs($professor)->post(route('diario.store'), payloadDiario($ids));
    $diario = Diario::first();

    $this->actingAs($professor)->put(route('diario.frequencia.salvar', $diario->id), [
        'frequencias' => [
            ['matricula_id' => $ids['matricula']->id, 'status' => 'justificada'],
        ],
    ])->assertSessionHasNoErrors();

    // sem registro previo justificado, vira falta comum
    expect(Frequencia::first()->status)->toBe('falta');
});

test('professor preserva falta justificada existente ao salvar chamada', function () {
    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupDiario($empresa);

    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $ids['turma']->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $professor->id,
        'papel' => 'titular',
    ]);

    $this->actingAs($admin)->post(route('diario.store'), payloadDiario($ids));
    $diario = Diario::first();

    // secretaria ja registrou a justificativa (status justificada)
    Frequencia::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'diario_id' => $diario->id, 'matricula_id' => $ids['matricula']->id,
        'status' => 'justificada', 'justificativa' => 'Atestado médico',
    ]);

    // professor salva a chamada sem tocar na linha (checkbox vermelho = falta visual,
    // mas o status real preservado e justificada)
    $this->actingAs($professor)->put(route('diario.frequencia.salvar', $diario->id), [
        'frequencias' => [
            ['matricula_id' => $ids['matricula']->id, 'status' => 'justificada'],
        ],
    ])->assertSessionHasNoErrors();

    expect(Frequencia::count())->toBe(1)
        ->and(Frequencia::first()->status)->toBe('justificada')
        ->and(Frequencia::first()->justificativa)->toBe('Atestado médico');
});

test('registrar aula trava turma do contexto e lista so as disciplinas dela', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupDiario($empresa);

    // segunda disciplina ofertada na mesma turma + uma fora dela
    $artes = Disciplina::create(['nome' => 'Artes', 'empresa_id' => $empresa->id]);
    DB::table('turma_disciplina')->insert([
        'turma_id' => $ids['turma']->id, 'disciplina_id' => $artes->id,
        'carga_horaria_semanal' => 2, 'created_at' => now(), 'updated_at' => now(),
    ]);
    Disciplina::create(['nome' => 'Fora da Turma', 'empresa_id' => $empresa->id]);

    // sem contexto: select de disciplina comeca vazio (depende da turma)
    $this->actingAs($admin)->get(route('diario.create'))
        ->assertOk()
        ->assertViewHas('turmaFixa', null)
        ->assertViewHas('disciplinas', fn ($d) => $d === []);

    // com turma_id: turma travada e disciplinas = so as ofertadas nela
    $response = $this->actingAs($admin)->get(route('diario.create', [
        'turma_id' => $ids['turma']->id, 'disciplina_id' => $ids['disciplina']->id,
        'data' => '2026-03-10', 'chamada' => '1',
    ]));
    $response->assertOk()
        ->assertViewHas('turmaFixa', fn ($t) => $t && $t->id === $ids['turma']->id)
        ->assertViewHas('disciplinas', fn ($d) => count($d) === 2
            && isset($d[$ids['disciplina']->id], $d[$artes->id]));
});

test('endpoint de disciplinas por turma respeita perfil e acesso', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $professor = usuarioTenant($empresa, 'professor');
    $ids = setupDiario($empresa);

    // admin recebe todas as ofertadas na turma
    $this->actingAs($admin)->getJson(route('diario.disciplinas', ['turma_id' => $ids['turma']->id]))
        ->assertOk()
        ->assertJsonFragment(['Matemática'])
        ->assertJsonCount(1);

    // professor sem atribuicao nao acessa a turma
    $this->actingAs($professor)->getJson(route('diario.disciplinas', ['turma_id' => $ids['turma']->id]))
        ->assertForbidden();

    // com atribuicao na disciplina, recebe so ela
    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $ids['turma']->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $professor->id,
        'papel' => 'titular',
    ]);
    $this->actingAs($professor)->getJson(route('diario.disciplinas', ['turma_id' => $ids['turma']->id]))
        ->assertOk()
        ->assertJsonCount(1);
});
