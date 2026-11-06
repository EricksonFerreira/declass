<?php

use App\Models\AnoLetivo;
use App\Models\Avaliacao;
use App\Models\Disciplina;
use App\Models\Escola;
use App\Models\Matricula;
use App\Models\PeriodoLetivo;
use App\Models\Turma;
use App\Support\EmpresaContext;

function setupPeriodoLetivo($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Central', 'empresa_id' => $empresa->id]);
    $anoLetivo = AnoLetivo::create([
        'empresa_id' => $empresa->id, 'escola_id' => $escola->id,
        'ano' => 2026, 'data_inicio' => '2026-02-01', 'data_fim' => '2026-12-15',
        'status' => 'letivo', 'ativo' => 'S',
    ]);

    return compact('escola', 'anoLetivo');
}

function periodoDemo(array $ids, array $overrides = []): PeriodoLetivo
{
    return PeriodoLetivo::create(array_merge([
        'empresa_id' => $ids['empresa']->id,
        'escola_id' => $ids['escola']->id,
        'ano_letivo_id' => $ids['anoLetivo']->id,
        'nome' => '1º Bimestre',
        'ordem' => 1,
        'data_inicio' => '2026-02-01',
        'data_fim' => '2026-04-15',
        'ativo' => 'S',
    ], $overrides));
}

test('coordenador cadastra 4 bimestres configuraveis no ano letivo', function () {
    $empresa = tenant();
    $ids = setupPeriodoLetivo($empresa);
    $ids['empresa'] = $empresa;

    $user = usuarioTenant($empresa, 'coordenador_pedagogico');

    foreach ([1 => '1º Bimestre', 2 => '2º Bimestre', 3 => '3º Bimestre', 4 => '4º Bimestre'] as $ordem => $nome) {
        $this->actingAs($user)->post(route('periodo_letivo.store'), [
            'ano_letivo_id' => $ids['anoLetivo']->id,
            'nome' => $nome,
            'ordem' => $ordem,
            'data_inicio' => '2026-02-0'.$ordem,
            'data_fim' => '2026-0'.($ordem + 1).'-15',
            'ativo' => 'S',
        ])->assertRedirect();
    }

    expect(PeriodoLetivo::where('ano_letivo_id', $ids['anoLetivo']->id)->count())->toBe(4);

    $this->actingAs($user)->get(route('periodo_letivo.index'))
        ->assertOk()->assertSee('4º Bimestre');
});

test('ordem duplicada no mesmo ano letivo e rejeitada', function () {
    $empresa = tenant();
    $ids = setupPeriodoLetivo($empresa);
    $ids['empresa'] = $empresa;
    periodoDemo($ids);

    $user = usuarioTenant($empresa, 'administrador');

    $this->actingAs($user)->post(route('periodo_letivo.store'), [
        'ano_letivo_id' => $ids['anoLetivo']->id,
        'nome' => 'Outro',
        'ordem' => 1,
        'ativo' => 'S',
    ])->assertSessionHasErrors('ordem');
});

test('configuracao alternativa: 2 semestres de faculdade', function () {
    $empresa = tenant();
    $ids = setupPeriodoLetivo($empresa);
    $ids['empresa'] = $empresa;

    $user = usuarioTenant($empresa, 'administrador');

    foreach ([1 => '1º Semestre', 2 => '2º Semestre'] as $ordem => $nome) {
        $this->actingAs($user)->post(route('periodo_letivo.store'), [
            'ano_letivo_id' => $ids['anoLetivo']->id,
            'nome' => $nome,
            'ordem' => $ordem,
            'data_inicio' => $ordem === 1 ? '2026-02-01' : '2026-07-20',
            'data_fim' => $ordem === 1 ? '2026-07-15' : '2026-12-10',
            'ativo' => 'S',
        ])->assertRedirect();
    }

    expect(PeriodoLetivo::where('ano_letivo_id', $ids['anoLetivo']->id)->count())->toBe(2)
        ->and(PeriodoLetivo::where('nome', '2º Semestre')->exists())->toBeTrue();
});

test('avaliacao vinculada a periodo letivo sincroniza o rotulo texto', function () {
    $empresa = tenant();
    $ids = setupPeriodoLetivo($empresa);
    $ids['empresa'] = $empresa;
    $periodo = periodoDemo($ids, ['nome' => '2º Bimestre']);

    $turma = Turma::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'ano_letivo_id' => $ids['anoLetivo']->id,
        'etapa_id' => \App\Models\Etapa::create([
            'empresa_id' => $empresa->id, 'nome' => 'Fundamental I',
            'segmento' => 'fundamental', 'ordem' => 1, 'ativo' => 'S',
        ])->id,
        'nome' => '5º Ano A', 'ativo' => 'S',
    ]);
    $disciplina = Disciplina::create([
        'empresa_id' => $empresa->id, 'nome' => 'Matemática', 'sigla' => 'MAT',
    ]);
    DB::table('turma_disciplina')->insert([
        'turma_id' => $turma->id, 'disciplina_id' => $disciplina->id,
        'carga_horaria_semanal' => 5, 'created_at' => now(), 'updated_at' => now(),
    ]);

    $pessoa = \App\Models\Pessoa::create(['nome' => 'Aluno', 'empresa_id' => $empresa->id]);
    $aluno = \App\Models\Aluno::create([
        'empresa_id' => $empresa->id, 'pessoa_id' => $pessoa->id,
        'escola_id' => $ids['escola']->id, 'registro_academico' => 'RA-1', 'ativo' => 'S',
    ]);
    Matricula::create([
        'empresa_id' => $empresa->id, 'aluno_id' => $aluno->id,
        'turma_id' => $turma->id, 'ano_letivo_id' => $ids['anoLetivo']->id,
        'status' => 'ativa', 'numero_chamada' => 1,
    ]);

    $user = usuarioTenant($empresa, 'professor');
    // professor precisa de atribuicao para ver a turma no form
    \App\Models\Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $turma->id,
        'disciplina_id' => $disciplina->id, 'user_id' => $user->id,
        'papel' => 'titular', 'ativo' => 'S',
    ]);

    $this->actingAs($user)->post(route('avaliacao.store'), [
        'turma_id' => $turma->id,
        'disciplina_id' => $disciplina->id,
        'nome' => 'Prova 2',
        'tipo' => 'prova',
        'periodo_letivo_id' => $periodo->id,
        'nota_maxima' => 10,
    ])->assertRedirect();

    $avaliacao = Avaliacao::where('nome', 'Prova 2')->first();
    expect($avaliacao->periodo_letivo_id)->toBe($periodo->id)
        ->and($avaliacao->periodo)->toBe('2º Bimestre')
        ->and($avaliacao->periodoLetivo->id)->toBe($periodo->id);
});

test('periodo letivo de outro tenant nao pode ser vinculado', function () {
    $empresa = tenant();
    $ids = setupPeriodoLetivo($empresa);
    $ids['empresa'] = $empresa;

    $outro = tenant();
    app(EmpresaContext::class)->setEmpresaId($outro->id);
    $escola2 = Escola::create(['nome' => 'Outra', 'empresa_id' => $outro->id]);
    $ano2 = AnoLetivo::create([
        'empresa_id' => $outro->id, 'escola_id' => $escola2->id,
        'ano' => 2026, 'status' => 'letivo', 'ativo' => 'S',
    ]);
    $periodoOutro = periodoDemo([
        'empresa' => $outro, 'escola' => $escola2, 'anoLetivo' => $ano2,
    ], ['nome' => '1º Semestre', 'ordem' => 1]);

    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $user = usuarioTenant($empresa, 'administrador');

    $this->actingAs($user)->post(route('periodo_letivo.store'), [
        'ano_letivo_id' => $ids['anoLetivo']->id,
        'nome' => 'Invasao',
        'ordem' => 9,
        'periodo_letivo_id' => $periodoOutro->id, // campo estranho — ignorado
        'ativo' => 'S',
    ])->assertRedirect();

    // O periodo do outro tenant continua intacto e nao foi duplicado
    // (DB::table ignora o global scope de tenant do model)
    expect(DB::table('periodos_letivos')->where('empresa_id', $outro->id)->count())->toBe(1);
});
