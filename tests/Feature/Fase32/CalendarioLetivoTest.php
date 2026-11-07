<?php

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\Atribuicao;
use App\Models\Disciplina;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Evento;
use App\Models\Feriado;
use App\Models\Horario;
use App\Models\HorarioSlot;
use App\Models\HorarioTurma;
use App\Models\Matricula;
use App\Models\Pessoa;
use App\Models\Responsavel;
use App\Models\Turma;
use App\Services\CalendarioLetivoService;
use App\Support\EmpresaContext;

function setupCal($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Cal', 'empresa_id' => $empresa->id]);
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

function horarioSegSex($empresa, $escola, string $nome, string $turno = 'matutino'): Horario
{
    $horario = Horario::create([
        'empresa_id' => $empresa->id, 'escola_id' => $escola->id,
        'turno' => $turno, 'nome' => $nome, 'ativo' => 'S',
    ]);
    foreach ([1, 2, 3, 4, 5] as $dia) {
        HorarioSlot::create([
            'empresa_id' => $empresa->id, 'escola_id' => $escola->id,
            'horario_id' => $horario->id, 'turno' => $turno,
            'dia_semana' => $dia, 'ordem' => 1,
            'hora_inicio' => '07:30', 'hora_fim' => '08:20',
        ]);
    }

    return $horario;
}

function criarEvento($empresa, $escola, array $over = []): Evento
{
    return Evento::create(array_merge([
        'empresa_id' => $empresa->id, 'escola_id' => $escola->id,
        'titulo' => 'Evento', 'tipo' => 'evento',
        'data_inicio' => '2026-03-10', 'publico' => 'todos',
        'status' => 'agendado', 'suspende_aulas' => 'N',
    ], $over));
}

// ---- Horarios multiplos por turno ------------------------------------

test('turno pode ter mais de um horario e a turma se vincula a um especifico', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupCal($empresa);

    // cria dois horarios matutinos distintos pela tela da matriz
    $this->actingAs($admin)->post(route('horario_matriz.store', $ids['escola']->id), [
        'nome' => 'Matutino Regular', 'turno' => 'matutino',
        'dias' => [1, 2, 3, 4, 5],
        'aulas' => [['inicio' => '07:30', 'fim' => '08:20']],
    ])->assertSessionHasNoErrors();

    $this->actingAs($admin)->post(route('horario_matriz.store', $ids['escola']->id), [
        'nome' => 'Matutino Contraturno', 'turno' => 'matutino',
        'dias' => [1, 3, 5],
        'aulas' => [['inicio' => '10:00', 'fim' => '10:50']],
    ])->assertSessionHasNoErrors();

    $horarios = Horario::where('escola_id', $ids['escola']->id)->orderBy('id')->get();
    expect($horarios)->toHaveCount(2);
    expect($horarios[0]->slots)->toHaveCount(5)
        ->and($horarios[1]->slots)->toHaveCount(3);

    // turma vinculada ao contraturno usa os slots dele
    $turma = $ids['turma'];
    $turma->update(['horario_id' => $horarios[1]->id]);

    $service = app(CalendarioLetivoService::class);
    expect($service->diasLetivosDaTurma($turma->fresh()))->toBe([1, 3, 5]);
});

test('nao exclui horario com turma vinculada', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupCal($empresa);
    $horario = horarioSegSex($empresa, $ids['escola'], 'Matutino A');
    $ids['turma']->update(['horario_id' => $horario->id]);

    $this->actingAs($admin)->delete(route('horario_matriz.destroy', $horario->id));
    expect(Horario::find($horario->id))->not->toBeNull();
});

test('cadastro de turma aceita horario_id da mesma escola', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupCal($empresa);
    $horario = horarioSegSex($empresa, $ids['escola'], 'Matutino A');
    $outraEscola = Escola::create(['nome' => 'Outra', 'empresa_id' => $empresa->id]);
    $horarioOutra = horarioSegSex($empresa, $outraEscola, 'Outro');

    $this->actingAs($admin)->post(route('turma.store'), [
        'escola_id' => $ids['escola']->id,
        'ano_letivo_id' => $ids['anoLetivo']->id,
        'etapa_id' => $ids['etapa']->id,
        'nome' => 'Turma Nova', 'turno' => 'matutino', 'ativo' => 'S',
        'horario_id' => $horario->id,
    ])->assertSessionHasNoErrors();
    expect(Turma::where('nome', 'Turma Nova')->first()->horario_id)->toBe($horario->id);

    // horario de outra escola e rejeitado
    $this->actingAs($admin)->post(route('turma.store'), [
        'escola_id' => $ids['escola']->id,
        'ano_letivo_id' => $ids['anoLetivo']->id,
        'etapa_id' => $ids['etapa']->id,
        'nome' => 'Turma Ruim', 'turno' => 'matutino', 'ativo' => 'S',
        'horario_id' => $horarioOutra->id,
    ])->assertSessionHasErrors('horario_id');
});

// ---- Feriados e eventos no calendario ---------------------------------

test('feriado que suspende aulas aparece como sem aula no calendario', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupCal($empresa);
    $horario = horarioSegSex($empresa, $ids['escola'], 'Matutino A');
    $ids['turma']->update(['horario_id' => $horario->id]);

    // 2026-03-10 e terca (dia letivo)
    $this->actingAs($admin)->post(route('feriado.store'), [
        'nome' => 'Feriado Municipal', 'data_inicio' => '2026-03-10',
        'suspende_aulas' => 'S',
    ])->assertSessionHasNoErrors();

    $service = app(CalendarioLetivoService::class);
    $dias = $service->calendarioDaTurma(
        $ids['turma']->fresh(),
        Carbon\Carbon::parse('2026-03-09'),
        Carbon\Carbon::parse('2026-03-15')
    );

    expect($dias['2026-03-09']['status'])->toBe('aula')          // segunda normal
        ->and($dias['2026-03-10']['status'])->toBe('sem_aula')   // feriado suspenso
        ->and($dias['2026-03-10']['feriados']->first()->nome)->toBe('Feriado Municipal')
        ->and($dias['2026-03-14']['status'])->toBe('nao_letivo'); // sabado sem slots
});

test('evento pode ser por turma e informar se suspende ou nao a aula', function () {
    $empresa = tenant();
    $ids = setupCal($empresa);
    $horario = horarioSegSex($empresa, $ids['escola'], 'Matutino A');
    $ids['turma']->update(['horario_id' => $horario->id]);
    $turmaB = Turma::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'ano_letivo_id' => $ids['anoLetivo']->id, 'etapa_id' => $ids['etapa']->id,
        'nome' => '1º Ano B', 'turno' => 'matutino', 'ativo' => 'S',
        'horario_id' => $horario->id,
    ]);

    // Excursao so da turma A: suspende aulas dela, nao aparece na B
    $excursao = criarEvento($empresa, $ids['escola'], [
        'titulo' => 'Excursão 1ºA', 'suspende_aulas' => 'S',
    ]);
    $excursao->turmas()->sync([$ids['turma']->id]);

    // Festa junina da escola toda: convive com aula normal
    criarEvento($empresa, $ids['escola'], [
        'titulo' => 'Festa Junina', 'data_inicio' => '2026-03-11', 'suspende_aulas' => 'N',
    ]);

    $service = app(CalendarioLetivoService::class);
    $diasA = $service->calendarioDaTurma($ids['turma']->fresh(),
        Carbon\Carbon::parse('2026-03-09'), Carbon\Carbon::parse('2026-03-15'));
    $diasB = $service->calendarioDaTurma($turmaB,
        Carbon\Carbon::parse('2026-03-09'), Carbon\Carbon::parse('2026-03-15'));

    expect($diasA['2026-03-10']['status'])->toBe('sem_aula')          // excursao suspende
        ->and($diasA['2026-03-11']['status'])->toBe('aula_evento')    // festa com aula
        ->and($diasB['2026-03-10']['status'])->toBe('aula')           // excursao nao e da B
        ->and($diasB['2026-03-10']['eventos'])->toHaveCount(0)
        ->and($diasB['2026-03-11']['status'])->toBe('aula_evento');   // festa e de todos
});

test('evento em dia nao letivo vira so evento', function () {
    $empresa = tenant();
    $ids = setupCal($empresa);
    $horario = horarioSegSex($empresa, $ids['escola'], 'Matutino A');
    $ids['turma']->update(['horario_id' => $horario->id]);

    // 2026-03-14 e sabado
    criarEvento($empresa, $ids['escola'], [
        'titulo' => 'Mutirão de Limpeza', 'data_inicio' => '2026-03-14',
    ]);

    $dias = app(CalendarioLetivoService::class)->calendarioDaTurma(
        $ids['turma']->fresh(),
        Carbon\Carbon::parse('2026-03-14'), Carbon\Carbon::parse('2026-03-15'));

    expect($dias['2026-03-14']['status'])->toBe('evento')
        ->and($dias['2026-03-15']['status'])->toBe('nao_letivo');
});

// ---- Telas por perfil --------------------------------------------------

test('professor ve calendario so das turmas que leciona', function () {
    $empresa = tenant();
    $prof = usuarioTenant($empresa, 'professor');
    $outroProf = usuarioTenant($empresa, 'professor');
    $ids = setupCal($empresa);
    horarioSegSex($empresa, $ids['escola'], 'Matutino A');
    $turmaB = Turma::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'ano_letivo_id' => $ids['anoLetivo']->id, 'etapa_id' => $ids['etapa']->id,
        'nome' => 'Turma do Outro', 'turno' => 'matutino', 'ativo' => 'S',
    ]);

    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $ids['turma']->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $prof->id, 'papel' => 'titular',
    ]);
    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $turmaB->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $outroProf->id, 'papel' => 'titular',
    ]);

    // select do form so tem a turma do professor
    $this->actingAs($prof)->get(route('calendario.index'))
        ->assertOk()
        ->assertSee('1º Ano A')
        ->assertDontSee('Turma do Outro');

    // e nao abre a turma do colega nem por URL
    $this->actingAs($prof)->get(route('calendario.index', ['turma_id' => $turmaB->id]))
        ->assertForbidden();

    // admin ve todas
    $admin = usuarioTenant($empresa, 'administrador');
    $this->actingAs($admin)->get(route('calendario.index', ['turma_id' => $turmaB->id]))
        ->assertOk();
});

test('responsavel ve o calendario letivo do aluno no portal', function () {
    $empresa = tenant();
    $ids = setupCal($empresa);
    $horario = horarioSegSex($empresa, $ids['escola'], 'Matutino A');
    $ids['turma']->update(['horario_id' => $horario->id]);

    $pessoaAluno = Pessoa::create(['nome' => 'Pedro', 'empresa_id' => $empresa->id]);
    $aluno = Aluno::create([
        'pessoa_id' => $pessoaAluno->id, 'escola_id' => $ids['escola']->id,
        'registro_academico' => 'RA-1', 'empresa_id' => $empresa->id,
    ]);
    Matricula::create([
        'aluno_id' => $aluno->id, 'turma_id' => $ids['turma']->id,
        'ano_letivo_id' => $ids['anoLetivo']->id, 'status' => 'ativa',
        'numero_chamada' => 1, 'empresa_id' => $empresa->id,
    ]);

    $usuarioResp = usuarioTenant($empresa, 'responsavel_legal');
    $pessoaResp = Pessoa::create(['nome' => 'Mãe', 'empresa_id' => $empresa->id]);
    $resp = Responsavel::create([
        'pessoa_id' => $pessoaResp->id, 'user_id' => $usuarioResp->id, 'empresa_id' => $empresa->id,
    ]);
    $aluno->responsaveis()->attach($resp->id, ['tipo' => 'legal', 'principal' => true]);

    Feriado::create([
        'empresa_id' => $empresa->id, 'nome' => 'Feriado X',
        'data_inicio' => '2026-03-10', 'suspende_aulas' => 'S',
    ]);
    criarEvento($empresa, $ids['escola'], ['titulo' => 'Feira de Ciências', 'data_inicio' => '2026-03-12']);

    $this->actingAs($usuarioResp)
        ->get(route('portal.calendario', ['mes' => '2026-03', 'aluno_id' => $aluno->id]))
        ->assertOk()
        ->assertSee('Feriado X')
        ->assertSee('Feira de Ciências');
});

test('evento restrito a outra turma nao aparece no portal do aluno', function () {
    $empresa = tenant();
    $ids = setupCal($empresa);
    $horario = horarioSegSex($empresa, $ids['escola'], 'Matutino A');
    $ids['turma']->update(['horario_id' => $horario->id]);
    $turmaB = Turma::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'ano_letivo_id' => $ids['anoLetivo']->id, 'etapa_id' => $ids['etapa']->id,
        'nome' => 'Turma B', 'turno' => 'matutino', 'ativo' => 'S',
    ]);

    $pessoaAluno = Pessoa::create(['nome' => 'Ana', 'empresa_id' => $empresa->id]);
    $aluno = Aluno::create([
        'pessoa_id' => $pessoaAluno->id, 'escola_id' => $ids['escola']->id,
        'registro_academico' => 'RA-2', 'empresa_id' => $empresa->id,
    ]);
    Matricula::create([
        'aluno_id' => $aluno->id, 'turma_id' => $ids['turma']->id,
        'ano_letivo_id' => $ids['anoLetivo']->id, 'status' => 'ativa',
        'numero_chamada' => 1, 'empresa_id' => $empresa->id,
    ]);

    $usuarioResp = usuarioTenant($empresa, 'responsavel_legal');
    $resp = Responsavel::create([
        'pessoa_id' => Pessoa::create(['nome' => 'Pai', 'empresa_id' => $empresa->id])->id,
        'user_id' => $usuarioResp->id, 'empresa_id' => $empresa->id,
    ]);
    $aluno->responsaveis()->attach($resp->id, ['tipo' => 'legal', 'principal' => true]);

    // evento so da turma B (publico, mas fora do alcance do aluno)
    $ev = criarEvento($empresa, $ids['escola'], ['titulo' => 'Festa da Turma B', 'data_inicio' => '2026-03-12']);
    $ev->turmas()->sync([$turmaB->id]);

    $this->actingAs($usuarioResp)
        ->get(route('portal.calendario', ['mes' => '2026-03', 'aluno_id' => $aluno->id]))
        ->assertOk()
        ->assertDontSee('Festa da Turma B');
});

// ---- Navegacao por mes e destaque do professor -------------------------

test('calendario exibe um mes por vez e respeita o parametro mes', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupCal($empresa);
    horarioSegSex($empresa, $ids['escola'], 'Matutino A');

    // mes explicito dentro do ano letivo
    $response = $this->actingAs($admin)->get(route('calendario.index', [
        'turma_id' => $ids['turma']->id, 'mes' => '2026-03',
    ]));
    $response->assertOk()
        ->assertViewHas('mesSelecionado', '2026-03')
        ->assertViewHas('mesExibido', fn ($m) => $m['chave'] === '2026-03');

    // mes fora do periodo cai no mes atual ou no primeiro disponivel
    $response2 = $this->actingAs($admin)->get(route('calendario.index', [
        'turma_id' => $ids['turma']->id, 'mes' => '2030-01',
    ]));
    expect($response2->viewData('mesSelecionado'))->not->toBe('2030-01');

    // navegacao anterior/proximo dentro do periodo
    expect($response->viewData('mesAnterior'))->toBe('2026-02')
        ->and($response->viewData('proximoMes'))->toBe('2026-04');
});

test('professor ve destaque dos dias e aulas que leciona na turma', function () {
    $empresa = tenant();
    $prof = usuarioTenant($empresa, 'professor');
    $ids = setupCal($empresa);
    $horario = horarioSegSex($empresa, $ids['escola'], 'Matutino A');
    $ids['turma']->update(['horario_id' => $horario->id]);

    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $ids['turma']->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $prof->id, 'papel' => 'titular',
    ]);

    // professor leciona seg (dia 1) e qua (dia 3), ordem 1 do slot
    foreach ([1, 3] as $dia) {
        HorarioTurma::create([
            'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
            'turma_id' => $ids['turma']->id, 'dia_semana' => $dia, 'ordem' => 1,
            'disciplina_id' => $ids['disciplina']->id, 'user_id' => $prof->id,
            'sala' => '101',
        ]);
    }

    $response = $this->actingAs($prof)->get(route('calendario.index', [
        'turma_id' => $ids['turma']->id, 'mes' => '2026-03',
    ]));
    $response->assertOk()
        ->assertViewHas('diasProfessor', fn ($d) => in_array(1, $d) && in_array(3, $d) && ! in_array(2, $d))
        ->assertViewHas('aulasProfessor', fn ($a) => $a[1]->first()['disciplina'] === 'MAT'
            && $a[1]->first()['inicio'] === '07:30');

    // flag "so dias que leciono"
    $this->actingAs($prof)->get(route('calendario.index', [
        'turma_id' => $ids['turma']->id, 'mes' => '2026-03', 'minhas_aulas' => '1',
    ]))->assertViewHas('somenteMinhas', true);

    // admin nao recebe dados de professor
    $admin = usuarioTenant($empresa, 'administrador');
    $this->actingAs($admin)->get(route('calendario.index', [
        'turma_id' => $ids['turma']->id, 'mes' => '2026-03',
    ]))->assertViewHas('diasProfessor', fn ($d) => $d === []);
});
