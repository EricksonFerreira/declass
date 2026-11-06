<?php

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\Atribuicao;
use App\Models\Avaliacao;
use App\Models\Disciplina;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Matricula;
use App\Models\Nota;
use App\Models\Pessoa;
use App\Models\Turma;
use App\Support\EmpresaContext;
use Illuminate\Support\Facades\DB;

function setupAvaliacao($empresa): array
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
        'turma_id' => $turma->id,
        'disciplina_id' => $disciplina->id,
        'carga_horaria_semanal' => 5,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

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

    return compact('escola', 'anoLetivo', 'etapa', 'turma', 'disciplina', 'aluno', 'matricula');
}

function payloadAvaliacao(array $ids, array $overrides = []): array
{
    return array_merge([
        'turma_id' => $ids['turma']->id,
        'disciplina_id' => $ids['disciplina']->id,
        'tipo' => 'prova',
        'nome' => 'Prova 1',
        'periodo' => '1º bim.',
        'data' => '2026-03-15',
        'nota_maxima' => 10,
    ], $overrides);
}

test('professor cria avaliacao e lanca notas', function () {
    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');
    $ids = setupAvaliacao($empresa);

    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $ids['turma']->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $professor->id,
        'papel' => 'titular',
    ]);

    $this->actingAs($professor)->get(route('avaliacao.index'))->assertOk();

    $this->actingAs($professor)->post(route('avaliacao.store'), payloadAvaliacao($ids))
        ->assertSessionHasNoErrors();

    $avaliacao = Avaliacao::first();
    expect($avaliacao)->not->toBeNull()
        ->and($avaliacao->user_id)->toBe($professor->id)
        ->and($avaliacao->nota_maxima)->toBe(10.0);

    // grade de notas
    $this->actingAs($professor)->put(route('avaliacao.notas.salvar', $avaliacao->id), [
        'notas' => [
            ['matricula_id' => $ids['matricula']->id, 'nota' => 8.5, 'observacao' => null],
        ],
    ])->assertSessionHasNoErrors();

    expect(Nota::count())->toBe(1)
        ->and(Nota::first()->nota)->toBe(8.5);
});

test('avaliacao rejeita disciplina nao ofertada', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupAvaliacao($empresa);

    $outra = Disciplina::create(['nome' => 'História', 'empresa_id' => $empresa->id]);

    $this->actingAs($admin)->post(route('avaliacao.store'), payloadAvaliacao($ids, [
        'disciplina_id' => $outra->id,
    ]))->assertSessionHasErrors('disciplina_id');
});

test('nota acima da nota maxima e rejeitada', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupAvaliacao($empresa);

    $this->actingAs($admin)->post(route('avaliacao.store'), payloadAvaliacao($ids));
    $avaliacao = Avaliacao::first();

    $this->actingAs($admin)->put(route('avaliacao.notas.salvar', $avaliacao->id), [
        'notas' => [
            ['matricula_id' => $ids['matricula']->id, 'nota' => 15],
        ],
    ])->assertSessionHasErrors('notas');

    expect(Nota::count())->toBe(0);
});

test('nota vazia remove registro existente (upsert)', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupAvaliacao($empresa);

    $this->actingAs($admin)->post(route('avaliacao.store'), payloadAvaliacao($ids));
    $avaliacao = Avaliacao::first();

    $this->actingAs($admin)->put(route('avaliacao.notas.salvar', $avaliacao->id), [
        'notas' => [['matricula_id' => $ids['matricula']->id, 'nota' => 7]],
    ]);
    expect(Nota::count())->toBe(1);

    $this->actingAs($admin)->put(route('avaliacao.notas.salvar', $avaliacao->id), [
        'notas' => [['matricula_id' => $ids['matricula']->id, 'nota' => null]],
    ]);
    expect(Nota::count())->toBe(0);
});

test('lancamento rejeita matricula de outra turma', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupAvaliacao($empresa);

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

    $this->actingAs($admin)->post(route('avaliacao.store'), payloadAvaliacao($ids));
    $avaliacao = Avaliacao::first();

    $this->actingAs($admin)->put(route('avaliacao.notas.salvar', $avaliacao->id), [
        'notas' => [['matricula_id' => $matricula2->id, 'nota' => 9]],
    ])->assertSessionHasErrors('notas');
});

test('professor nao edita avaliacao de outro professor', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $professorB = usuarioTenant($empresa, 'professor');
    $ids = setupAvaliacao($empresa);

    $this->actingAs($admin)->post(route('avaliacao.store'), payloadAvaliacao($ids));
    $avaliacao = Avaliacao::first();

    $this->actingAs($professorB)->get(route('avaliacao.edit', $avaliacao->id))->assertForbidden();
    $this->actingAs($professorB)->get(route('avaliacao.notas', $avaliacao->id))->assertForbidden();
});

test('media da disciplina e calculada na grade', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupAvaliacao($empresa);

    // duas avaliacoes na mesma turma+disciplina
    $this->actingAs($admin)->post(route('avaliacao.store'), payloadAvaliacao($ids));
    $av1 = Avaliacao::first();
    $this->actingAs($admin)->post(route('avaliacao.store'), payloadAvaliacao($ids, [
        'nome' => 'Prova 2', 'data' => '2026-04-10',
    ]));
    $av2 = Avaliacao::latest('id')->first();

    $this->actingAs($admin)->put(route('avaliacao.notas.salvar', $av1->id), [
        'notas' => [['matricula_id' => $ids['matricula']->id, 'nota' => 8]],
    ]);
    $this->actingAs($admin)->put(route('avaliacao.notas.salvar', $av2->id), [
        'notas' => [['matricula_id' => $ids['matricula']->id, 'nota' => 6]],
    ]);

    $response = $this->actingAs($admin)->get(route('avaliacao.notas', $av1->id));
    $response->assertOk();
    expect($response->viewData('medias')[$ids['matricula']->id])->toBe(7.0);
});

test('professor ve avaliacoes das turmas que leciona', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $professor = usuarioTenant($empresa, 'professor');
    $ids = setupAvaliacao($empresa);

    // avaliacao criada pelo admin na turma
    $this->actingAs($admin)->post(route('avaliacao.store'), payloadAvaliacao($ids));

    // sem atribuicao: index vazio
    $response = $this->actingAs($professor)->get(route('avaliacao.index'));
    expect($response->viewData('avaliacoes')->total())->toBe(0);

    // com atribuicao: ve a avaliacao da turma mesmo criada por outro usuario
    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $ids['turma']->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $professor->id,
        'papel' => 'titular',
    ]);

    $response = $this->actingAs($professor)->get(route('avaliacao.index'));
    expect($response->viewData('avaliacoes')->total())->toBe(1);
});

test('professor nao cria avaliacao em turma que nao leciona', function () {
    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');
    $ids = setupAvaliacao($empresa);

    $this->actingAs($professor)->post(route('avaliacao.store'), payloadAvaliacao($ids))
        ->assertSessionHasErrors('turma_id');
    expect(Avaliacao::count())->toBe(0);
});

test('avaliacoes tem escopo de tenant', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupAvaliacao($empresa);

    $this->actingAs($admin)->post(route('avaliacao.store'), payloadAvaliacao($ids));
    expect(Avaliacao::count())->toBe(1);

    app(EmpresaContext::class)->setEmpresaId(tenant()->id);
    expect(Avaliacao::count())->toBe(0);
});
