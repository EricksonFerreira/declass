<?php

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\Avaliacao;
use App\Models\Disciplina;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Matricula;
use App\Models\Nota;
use App\Models\PeriodoLetivo;
use App\Models\Pessoa;
use App\Models\Turma;
use App\Support\EmpresaContext;

function historicoEscolarFixture($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Unidade Central', 'empresa_id' => $empresa->id]);
    $anoLetivo = AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => 2026,
        'data_inicio' => '2026-02-01', 'data_fim' => '2026-12-15',
        'status' => 'letivo', 'periodicidade' => 'semestral',
        'empresa_id' => $empresa->id,
    ]);
    $semestre1 = PeriodoLetivo::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'nome' => '1º Semestre', 'ordem' => 1, 'ativo' => 'S',
        'empresa_id' => $empresa->id,
    ]);
    $semestre2 = PeriodoLetivo::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'nome' => '2º Semestre', 'ordem' => 2, 'ativo' => 'S',
        'empresa_id' => $empresa->id,
    ]);
    $etapa = Etapa::create(['nome' => '1º Ano EF', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id]);
    $turma = Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id, 'nome' => '1º Ano EF A', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);
    $matematica = Disciplina::create(['nome' => 'Matemática', 'empresa_id' => $empresa->id]);
    $portugues = Disciplina::create(['nome' => 'Português', 'empresa_id' => $empresa->id]);
    $turma->disciplinas()->attach($matematica->id, ['carga_horaria_semanal' => 5]);
    $turma->disciplinas()->attach($portugues->id, ['carga_horaria_semanal' => 4]);

    $pessoa = Pessoa::create(['empresa_id' => $empresa->id, 'nome' => 'Aluno Historico Escolar', 'data_nascimento' => '2018-05-10']);
    $aluno = Aluno::create([
        'empresa_id' => $empresa->id, 'pessoa_id' => $pessoa->id,
        'escola_id' => $escola->id, 'registro_academico' => 'RA-HESC',
        'data_ingresso' => '2026-02-01',
    ]);
    $matricula = Matricula::create([
        'empresa_id' => $empresa->id, 'escola_id' => $escola->id,
        'aluno_id' => $aluno->id, 'turma_id' => $turma->id,
        'ano_letivo_id' => $anoLetivo->id, 'status' => 'ativa',
        'data_matricula' => '2026-02-01', 'numero_chamada' => 3,
    ]);

    return compact('escola', 'anoLetivo', 'semestre1', 'semestre2', 'etapa', 'turma', 'matematica', 'portugues', 'pessoa', 'aluno', 'matricula');
}

test('historico escolar do ano exibe grade de disciplinas por periodo', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $fx = historicoEscolarFixture($empresa);
    extract($fx);

    $avaliacao = Avaliacao::create([
        'empresa_id' => $empresa->id, 'escola_id' => $escola->id,
        'turma_id' => $turma->id, 'disciplina_id' => $matematica->id,
        'user_id' => $admin->id, 'tipo' => 'prova', 'nome' => 'P1',
        'periodo_letivo_id' => $semestre1->id, 'data' => '2026-04-10',
        'nota_maxima' => 10,
    ]);
    Nota::create(['empresa_id' => $empresa->id, 'avaliacao_id' => $avaliacao->id, 'matricula_id' => $matricula->id, 'nota' => 8]);

    $avaliacao2 = Avaliacao::create([
        'empresa_id' => $empresa->id, 'escola_id' => $escola->id,
        'turma_id' => $turma->id, 'disciplina_id' => $matematica->id,
        'user_id' => $admin->id, 'tipo' => 'prova', 'nome' => 'P2',
        'periodo_letivo_id' => $semestre2->id, 'data' => '2026-09-10',
        'nota_maxima' => 10,
    ]);
    Nota::create(['empresa_id' => $empresa->id, 'avaliacao_id' => $avaliacao2->id, 'matricula_id' => $matricula->id, 'nota' => 10]);

    $this->actingAs($admin)->get("/admin/aluno/{$aluno->id}/historico/escolar/{$matricula->id}")
        ->assertOk()
        ->assertSee('Aluno Historico Escolar')
        ->assertSee('2026')
        ->assertSee('1º Ano EF A')
        ->assertSee('Matemática')
        ->assertSee('Português')
        ->assertSee('1º Semestre')
        ->assertSee('2º Semestre')
        ->assertSee('80,0%')       // media do 1º semestre
        ->assertSee('100,0%')      // media do 2º semestre
        ->assertSee('90,0%');      // media final (media das duas)
});

test('historico escolar inclui matriculas de anos anteriores concluidas', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $fx = historicoEscolarFixture($empresa);
    extract($fx);

    // matricula concluida no ano anterior (2025) — historico deve cobrir
    $ano2025 = AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => 2025,
        'data_inicio' => '2025-02-01', 'data_fim' => '2025-12-15',
        'status' => 'encerrado', 'periodicidade' => 'anual',
        'empresa_id' => $empresa->id,
    ]);
    $etapa2025 = Etapa::create(['nome' => 'Educação Infantil', 'segmento' => 'infantil', 'empresa_id' => $empresa->id]);
    $turma2025 = Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $ano2025->id,
        'etapa_id' => $etapa2025->id, 'nome' => 'Jardim II', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);
    $matricula2025 = Matricula::create([
        'empresa_id' => $empresa->id, 'escola_id' => $escola->id,
        'aluno_id' => $aluno->id, 'turma_id' => $turma2025->id,
        'ano_letivo_id' => $ano2025->id, 'status' => 'concluida',
        'data_matricula' => '2025-02-01', 'numero_chamada' => 5,
    ]);

    // a grade do ano antigo abre normalmente (status nao e mais 'ativa')
    $this->actingAs($admin)->get("/admin/aluno/{$aluno->id}/historico/escolar/{$matricula2025->id}")
        ->assertOk()
        ->assertSee('Jardim II')
        ->assertSee('Concluída');

    // e o historico geral lista o botao do ano antigo tambem
    $this->actingAs($admin)->get("/admin/aluno/{$aluno->id}/historico")
        ->assertOk()
        ->assertSee("historico/escolar/{$matricula2025->id}", false);
});

test('pdf do historico escolar completo cobre todas as matriculas', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $fx = historicoEscolarFixture($empresa);
    extract($fx);

    $response = $this->actingAs($admin)->get("/admin/aluno/{$aluno->id}/historico/escolar/pdf");
    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('pdf');
});

test('pdf do historico escolar aceita filtrar por uma matricula', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $fx = historicoEscolarFixture($empresa);
    extract($fx);

    $this->actingAs($admin)->get("/admin/aluno/{$aluno->id}/historico/escolar/pdf?matricula={$matricula->id}")
        ->assertOk();

    $this->actingAs($admin)->get("/admin/aluno/{$aluno->id}/historico/escolar/pdf?matricula=999999")
        ->assertNotFound();
});

test('matricula de outro aluno retorna 404 no historico escolar', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $fx = historicoEscolarFixture($empresa);
    extract($fx);

    // segundo aluno com sua propria matricula
    $pessoa2 = Pessoa::create(['empresa_id' => $empresa->id, 'nome' => 'Outro Aluno']);
    $aluno2 = Aluno::create([
        'empresa_id' => $empresa->id, 'pessoa_id' => $pessoa2->id,
        'escola_id' => $escola->id,
    ]);
    $matricula2 = Matricula::create([
        'empresa_id' => $empresa->id, 'escola_id' => $escola->id,
        'aluno_id' => $aluno2->id, 'turma_id' => $turma->id,
        'ano_letivo_id' => $anoLetivo->id, 'status' => 'ativa',
        'data_matricula' => '2026-02-01',
    ]);

    $this->actingAs($admin)->get("/admin/aluno/{$aluno->id}/historico/escolar/{$matricula2->id}")
        ->assertNotFound();
});

test('aluno de outro tenant retorna 404 no historico escolar', function () {
    $empresa = tenant();
    $empresaB = tenant();
    $user = usuarioTenant($empresa, 'administrador');

    app(EmpresaContext::class)->setEmpresaId($empresaB->id);
    $escolaB = Escola::create(['nome' => 'Outra', 'empresa_id' => $empresaB->id]);
    $pessoaB = Pessoa::create(['empresa_id' => $empresaB->id, 'nome' => 'Aluno B']);
    $alunoB = Aluno::create([
        'empresa_id' => $empresaB->id, 'pessoa_id' => $pessoaB->id,
        'escola_id' => $escolaB->id,
    ]);

    $this->actingAs($user)->get("/admin/aluno/{$alunoB->id}/historico/escolar/pdf")
        ->assertNotFound();
});
