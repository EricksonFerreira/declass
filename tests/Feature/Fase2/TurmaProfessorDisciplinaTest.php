<?php

use App\Models\AnoLetivo;
use App\Models\Atribuicao;
use App\Models\Avaliacao;
use App\Models\Diario;
use App\Models\Disciplina;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Turma;
use App\Support\EmpresaContext;
use Illuminate\Support\Facades\DB;

function setupTurmaProf($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Central', 'empresa_id' => $empresa->id]);
    $anoLetivo = AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => 2026, 'empresa_id' => $empresa->id, 'ativo' => 'S',
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
    $outraDisciplina = Disciplina::create(['nome' => 'História', 'sigla' => 'HIS', 'empresa_id' => $empresa->id]);

    foreach ([$disciplina, $outraDisciplina] as $d) {
        DB::table('turma_disciplina')->insert([
            'turma_id' => $turma->id, 'disciplina_id' => $d->id,
            'carga_horaria_semanal' => 5, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    return compact('escola', 'anoLetivo', 'etapa', 'turma', 'disciplina', 'outraDisciplina');
}

function disciplinasPorTurmaJson($response, int $turmaId): array
{
    $json = $response->viewData('disciplinasPorTurma');
    $mapa = is_string($json) ? json_decode($json, true) : $json;

    return $mapa[$turmaId] ?? [];
}

test('professor acessa as telas aninhadas de chamada, avaliacao e tarefa da turma', function () {
    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');
    $ids = setupTurmaProf($empresa);

    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $ids['turma']->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $professor->id,
        'papel' => 'titular',
    ]);

    // Rotas aninhadas exigem a permissao do nome da rota — o professor
    // tem as permissoes base que liberam as mesmas telas.
    $this->actingAs($professor)
        ->get(route('turma.chamada.create', $ids['turma']->id))
        ->assertOk();
    $this->actingAs($professor)
        ->get(route('turma.avaliacao.create', $ids['turma']->id))
        ->assertOk();
    $this->actingAs($professor)
        ->get(route('turma.tarefa.create', $ids['turma']->id))
        ->assertOk();
});

test('professor regente ve todas as disciplinas ofertadas no formulario de avaliacao', function () {
    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');
    $ids = setupTurmaProf($empresa);

    // Atribuicao sem disciplina = regente da turma.
    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $ids['turma']->id,
        'user_id' => $professor->id, 'papel' => 'titular',
    ]);

    $response = $this->actingAs($professor)
        ->get(route('turma.avaliacao.create', $ids['turma']->id))
        ->assertOk();

    $disciplinas = disciplinasPorTurmaJson($response, $ids['turma']->id);

    expect($disciplinas)->toHaveKey($ids['disciplina']->id)
        ->toHaveKey($ids['outraDisciplina']->id);
});

test('professor ve apenas as disciplinas que leciona no formulario de avaliacao', function () {
    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');
    $ids = setupTurmaProf($empresa);

    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $ids['turma']->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $professor->id,
        'papel' => 'titular',
    ]);

    $response = $this->actingAs($professor)
        ->get(route('turma.avaliacao.create', $ids['turma']->id))
        ->assertOk();

    $disciplinas = disciplinasPorTurmaJson($response, $ids['turma']->id);

    expect($disciplinas)->toHaveKey($ids['disciplina']->id)
        ->not->toHaveKey($ids['outraDisciplina']->id);
});

test('tela da disciplina lista aulas e avaliacoes da disciplina', function () {
    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');
    $ids = setupTurmaProf($empresa);

    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $ids['turma']->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $professor->id,
        'papel' => 'titular',
    ]);

    Diario::create([
        'empresa_id' => $empresa->id, 'turma_id' => $ids['turma']->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $professor->id,
        'data' => '2026-03-10', 'conteudo' => 'Frações equivalentes',
    ]);
    Avaliacao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $ids['turma']->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $professor->id,
        'tipo' => 'prova', 'nome' => 'Prova 1', 'data' => '2026-03-20',
    ]);

    $response = $this->actingAs($professor)
        ->get(route('turma.disciplina.show', [$ids['turma']->id, $ids['disciplina']->id]))
        ->assertOk();

    expect($response->viewData('diarios'))->toHaveCount(1)
        ->and($response->viewData('avaliacoes'))->toHaveCount(1);
});

test('professor nao abre disciplina que nao leciona na turma', function () {
    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');
    $ids = setupTurmaProf($empresa);

    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $ids['turma']->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $professor->id,
        'papel' => 'titular',
    ]);

    $this->actingAs($professor)
        ->get(route('turma.disciplina.show', [$ids['turma']->id, $ids['outraDisciplina']->id]))
        ->assertForbidden();
});
