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

    // professor ve os diarios da turma que leciona (o seu e o do admin)
    $response = $this->actingAs($professor)->get(route('diario.index'));
    $response->assertOk();
    $diariosVisiveis = $response->viewData('diarios');
    expect($diariosVisiveis->total())->toBe(2);

    // admin ve ambos
    $responseAdmin = $this->actingAs($admin)->get(route('diario.index'));
    expect($responseAdmin->viewData('diarios')->total())->toBe(2);
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
