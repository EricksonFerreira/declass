<?php

use App\Models\AnoLetivo;
use App\Models\Atribuicao;
use App\Models\Disciplina;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Horario;
use App\Models\HorarioSlot;
use App\Models\HorarioTurma;
use App\Models\Turma;
use App\Support\EmpresaContext;

function setupMh($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola MH', 'empresa_id' => $empresa->id]);
    $anoLetivo = AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => 2026, 'empresa_id' => $empresa->id,
        'data_inicio' => '2026-02-01', 'data_fim' => '2026-11-30',
    ]);
    $etapa = Etapa::create(['nome' => '1º Ano', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id]);
    $disciplina = Disciplina::create(['nome' => 'Mat', 'sigla' => 'MAT', 'empresa_id' => $empresa->id]);
    $turma = Turma::create([
        'empresa_id' => $empresa->id, 'escola_id' => $escola->id,
        'ano_letivo_id' => $anoLetivo->id, 'etapa_id' => $etapa->id,
        'nome' => '1º Ano A', 'turno' => 'matutino', 'ativo' => 'S',
    ]);

    return compact('escola', 'anoLetivo', 'etapa', 'disciplina', 'turma');
}

function horarioSegSexMh($empresa, $escola, string $nome): Horario
{
    $horario = Horario::create([
        'empresa_id' => $empresa->id, 'escola_id' => $escola->id,
        'turno' => 'matutino', 'nome' => $nome, 'ativo' => 'S',
    ]);
    foreach ([1, 2, 3, 4, 5] as $dia) {
        HorarioSlot::create([
            'empresa_id' => $empresa->id, 'escola_id' => $escola->id,
            'horario_id' => $horario->id, 'turno' => 'matutino',
            'dia_semana' => $dia, 'ordem' => 1,
            'hora_inicio' => '07:30', 'hora_fim' => '08:20',
        ]);
    }

    return $horario;
}

test('professor ve sua grade semanal com filtros de ano, escola e turno', function () {
    $empresa = tenant();
    $prof = usuarioTenant($empresa, 'professor');
    $outroProf = usuarioTenant($empresa, 'professor');
    $ids = setupMh($empresa);
    $horario = horarioSegSexMh($empresa, $ids['escola'], 'Matutino A');
    $ids['turma']->update(['horario_id' => $horario->id]);

    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $ids['turma']->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $prof->id, 'papel' => 'titular',
    ]);
    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $ids['turma']->id,
        'disciplina_id' => null, 'user_id' => $outroProf->id, 'papel' => 'titular',
    ]);

    // aula do professor: segunda 1o slot; aula do outro: terca
    HorarioTurma::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'turma_id' => $ids['turma']->id, 'dia_semana' => 1, 'ordem' => 1,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $prof->id, 'sala' => '101',
    ]);
    HorarioTurma::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'turma_id' => $ids['turma']->id, 'dia_semana' => 2, 'ordem' => 1,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $outroProf->id, 'sala' => '102',
    ]);

    // padrao: ano atual + matutino -> ve so a sua aula
    $this->actingAs($prof)->get(route('meus_horarios.index'))
        ->assertOk()
        ->assertSee('1º Ano A')
        ->assertSee('sala 101')
        ->assertDontSee('sala 102');

    // turno vespertino -> nao ha aulas dele ali
    $this->actingAs($prof)->get(route('meus_horarios.index', ['turno' => 'vespertino']))
        ->assertOk()
        ->assertSee('Nenhuma aula alocada');
});

test('meus horarios mostra aula de cada turma com seu proprio horario', function () {
    $empresa = tenant();
    $prof = usuarioTenant($empresa, 'professor');
    $ids = setupMh($empresa);
    $horario = horarioSegSexMh($empresa, $ids['escola'], 'Matutino A');
    $ids['turma']->update(['horario_id' => $horario->id]);
    $turmaB = Turma::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'ano_letivo_id' => $ids['anoLetivo']->id, 'etapa_id' => $ids['etapa']->id,
        'nome' => 'Turma B', 'turno' => 'matutino', 'ativo' => 'S', 'horario_id' => $horario->id,
    ]);
    $discB = Disciplina::create(['nome' => 'História', 'sigla' => 'HIS', 'empresa_id' => $empresa->id]);

    foreach ([$ids['turma'], $turmaB] as $t) {
        Atribuicao::create([
            'empresa_id' => $empresa->id, 'turma_id' => $t->id,
            'user_id' => $prof->id, 'papel' => 'titular',
        ]);
    }

    HorarioTurma::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'turma_id' => $ids['turma']->id, 'dia_semana' => 1, 'ordem' => 1,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $prof->id,
    ]);
    HorarioTurma::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'turma_id' => $turmaB->id, 'dia_semana' => 3, 'ordem' => 1,
        'disciplina_id' => $discB->id, 'user_id' => $prof->id,
    ]);

    $this->actingAs($prof)->get(route('meus_horarios.index'))
        ->assertOk()
        ->assertSee('1º Ano A')
        ->assertSee('Turma B')
        ->assertSee('HIS')
        ->assertSee('07:30–08:20');
});
